<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SppbStatus;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Models\DocumentValidation;
use App\Models\SppbHeader;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DocumentVerificationService
{
    /**
     * Derive the deterministic SHA256 verification token for a document page.
     *
     * Token = SHA256(generation_uuid + '-page-' + page_number)
     * This is computed the same way in both ProcessDocumentGenerationJob and DummyDocumentRenderer.
     */
    public static function deriveToken(string $generationUuid, int $pageNumber): string
    {
        return hash('sha256', $generationUuid.'-page-'.$pageNumber);
    }

    /**
     * Verify a document page using its SHA256 verification token (embedded in QR code).
     */
    public function verifyBySha256Token(string $sha256Token, ?string $ip = null, ?string $userAgent = null): array
    {
        $page = DocumentPage::with([
            'documentGeneration.plant',
            'documentGeneration.sppbHeader.department',
            'documentGeneration.sppbHeader.requester',
            'documentGeneration.sppbHeader.originLocation',
            'documentGeneration.sppbHeader.destinationLocation',
            'documentGeneration.sppbHeader.sppbDetails',
            'documentGeneration.sppbHeader.workflowInstances.workflowInstanceSteps',
            'documentGeneration.goodsRelease.sppbHeader',
        ])
            ->where('verification_token_hash', $sha256Token)
            ->first();

        $validationId = Str::uuid()->toString();

        if (! $page) {
            $this->logValidation(null, null, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent, $sha256Token);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $generation = $page->documentGeneration;

        if (! $generation) {
            $this->logValidation(null, $page->id, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent, $sha256Token);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $status = 'VALID';
        if ($generation->status === 'SUPERSEDED') {
            $status = 'SUPERSEDED';
        } elseif ($generation->status === 'REVOKED' || $generation->revoked_at !== null) {
            $status = 'REVOKED';
        } elseif ($generation->expires_at && $generation->expires_at->isPast()) {
            $status = 'EXPIRED';
        }

        $this->logValidation($generation->id, $page->id, $status, 'PUBLIC_QR', $validationId, $ip, $userAgent, $sha256Token);

        return [
            'status' => $status,
            'validation_id' => $validationId,
            'data' => $this->buildDetails($generation, $page),
        ];
    }

    /**
     * @deprecated Use verifyBySha256Token() instead.
     * Kept for backwards compatibility with older QR codes generated before SHA256 migration.
     */
    public function verifyPublicPage(string $verificationUuid, int $pageNumber, ?string $ip = null, ?string $userAgent = null): array
    {
        $page = DocumentPage::with([
            'documentGeneration.plant',
            'documentGeneration.sppbHeader.department',
            'documentGeneration.sppbHeader.requester',
            'documentGeneration.sppbHeader.originLocation',
            'documentGeneration.sppbHeader.destinationLocation',
            'documentGeneration.sppbHeader.sppbDetails',
            'documentGeneration.sppbHeader.workflowInstances.workflowInstanceSteps',
            'documentGeneration.goodsRelease.sppbHeader',
        ])
            ->where('verification_uuid', $verificationUuid)
            ->where('page_number', $pageNumber)
            ->first();

        $validationId = Str::uuid()->toString();

        if (! $page) {
            $this->logValidation(null, null, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $generation = $page->documentGeneration;

        if (! $generation) {
            $this->logValidation(null, $page->id, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $status = 'VALID';
        if ($generation->status === 'SUPERSEDED') {
            $status = 'SUPERSEDED';
        } elseif ($generation->status === 'REVOKED' || $generation->revoked_at !== null) {
            $status = 'REVOKED';
        } elseif ($generation->expires_at && $generation->expires_at->isPast()) {
            $status = 'EXPIRED';
        }

        $this->logValidation($generation->id, $page->id, $status, 'PUBLIC_QR', $validationId, $ip, $userAgent);

        return [
            'status' => $status,
            'validation_id' => $validationId,
            'data' => $this->buildDetails($generation, $page),
        ];
    }

    private function buildDetails(DocumentGeneration $generation, DocumentPage $page): array
    {
        $payload = $generation->render_payload ?? [];
        $sppb = $generation->sppbHeader
            ?? $generation->goodsRelease?->sppbHeader
            ?? $generation->goodsRelease?->sppbHeaders?->first();

        if ($sppb) {
            $sppb->loadMissing([
                'department',
                'requester',
                'originLocation',
                'destinationLocation',
                'sppbDetails',
                'currentWorkflowInstance.workflowInstanceSteps',
                'workflowInstances.workflowInstanceSteps',
            ]);
        }

        $requestDate = null;
        if ($sppb?->request_date) {
            $requestDate = is_string($sppb->request_date)
                ? $sppb->request_date
                : $sppb->request_date->format('Y-m-d');
        } elseif (isset($payload['request_date'])) {
            $requestDate = (string) $payload['request_date'];
        }

        $dateNeeded = null;
        if ($sppb?->date_needed) {
            $dateNeeded = is_string($sppb->date_needed)
                ? $sppb->date_needed
                : $sppb->date_needed->format('Y-m-d');
        } elseif (isset($payload['date_needed'])) {
            $dateNeeded = (string) $payload['date_needed'];
        } elseif (isset($payload['tanggal_kebutuhan'])) {
            $dateNeeded = (string) $payload['tanggal_kebutuhan'];
        }

        $purpose = null;
        if ($sppb) {
            if (! empty(trim((string) $sppb->purpose))) {
                $purpose = trim($sppb->purpose);
            } elseif (! empty(trim((string) $sppb->needed_name))) {
                $purpose = trim($sppb->needed_name);
            }
        }

        if (! $purpose && ! empty($payload)) {
            $candidates = [
                $payload['purpose'] ?? null,
                $payload['needed_name'] ?? null,
                $payload['keperluan'] ?? null,
                $payload['purpose_text'] ?? null,
                $payload['notes'] ?? null,
            ];
            foreach ($candidates as $candidate) {
                if (! empty(trim((string) $candidate))) {
                    $purpose = trim((string) $candidate);
                    break;
                }
            }
        }

        if (! $purpose) {
            $purpose = '—';
        }

        $origin = $sppb?->originLocation?->name ?? ($payload['locations']['origin'] ?? ($payload['origin_location'] ?? ($payload['lokasi_asal'] ?? '—')));
        $destination = $sppb?->destinationLocation?->name ?? ($payload['locations']['destination'] ?? ($payload['destination_location'] ?? ($payload['lokasi_tujuan'] ?? '—')));

        $totalTypes = 0;
        $totalQtyApproved = 0;

        if ($sppb?->sppbDetails && $sppb->sppbDetails->isNotEmpty()) {
            $totalTypes = $sppb->sppbDetails->count();
            $totalQtyApproved = (int) $sppb->sppbDetails->sum('quantity');
        } else {
            $totalTypes = (int) ($payload['items_summary']['total_item_types'] ?? ($payload['total_items'] ?? 0));
            $totalQtyApproved = (int) ($payload['items_summary']['total_quantity_approved'] ?? ($payload['total_quantity'] ?? 0));
        }

        return [
            'document_type' => $generation->document_type,
            'document_number' => $generation->document_number,
            'status_sppb' => $this->translateSppbStatus($sppb?->status ?? ($payload['status_sppb'] ?? ($generation->status === 'SUPERSEDED' ? 'SUPERSEDED' : 'APPROVED'))),
            'plant_name' => $generation->plant_name_snapshot ?? ($sppb?->plant?->name ?? '—'),
            'department_name' => $sppb?->department?->name ?? ($payload['department_name'] ?? ($payload['department'] ?? '—')),
            'requester_name' => $sppb?->requester?->name ?? ($payload['requester_name'] ?? ($payload['requester'] ?? '—')),
            'is_urgent' => (bool) ($sppb->is_urgent ?? ($payload['is_urgent'] ?? false)),
            'request_date' => $requestDate,
            'date_needed' => $dateNeeded,
            'purpose' => $purpose,
            'locations' => [
                'origin' => $origin,
                'destination' => $destination,
            ],
            'items_summary' => [
                'total_item_types' => $totalTypes,
                'total_quantity_approved' => $totalQtyApproved,
            ],
            'approval_summary' => $this->resolveApprovalSummary($sppb, $payload),
            'page_number' => $page->page_number,
            'total_pages' => $generation->page_count,
            'generated_at' => $this->formatTimestamp($generation->generated_at),
            'fingerprint' => substr($generation->checksum_sha256 ?? '', 0, 16),
        ];
    }

    private function resolveApprovalSummary(?SppbHeader $sppb, array $payload): array
    {
        if (isset($payload['approval_summary']) && is_array($payload['approval_summary'])) {
            return array_map(function (array $item): array {
                return [
                    'role' => $this->translateRole((string) ($item['role'] ?? 'Approver')),
                    'status' => $this->translateApprovalStatus((string) ($item['status'] ?? 'APPROVED')),
                    'approved_at' => isset($item['approved_at']) ? (string) $item['approved_at'] : null,
                ];
            }, $payload['approval_summary']);
        }

        if (! $sppb) {
            return [];
        }

        $instance = $sppb->currentWorkflowInstance ?? $sppb->workflowInstances->first();
        if (! $instance) {
            return [];
        }

        $summary = [];
        foreach ($instance->workflowInstanceSteps as $step) {
            if ($step->status === 'APPROVED' || $step->status === 'REJECTED') {
                $summary[] = [
                    'role' => $this->translateRole((string) ($step->name ?? 'Approver')),
                    'status' => $this->translateApprovalStatus((string) $step->status),
                    'approved_at' => $step->acted_at ? Carbon::parse($step->acted_at)->format('Y-m-d H:i') : null,
                ];
            }
        }

        return $summary;
    }

    private function translateSppbStatus(mixed $status): string
    {
        if ($status instanceof SppbStatus) {
            return mb_strtoupper($status->label(), 'UTF-8');
        }

        $strStatus = (string) $status;
        $enum = SppbStatus::tryFrom($strStatus);
        if ($enum) {
            return mb_strtoupper($enum->label(), 'UTF-8');
        }

        return match (strtoupper(trim($strStatus))) {
            'APPROVED' => 'DISETUJUI',
            'REJECTED' => 'DITOLAK',
            'PENDING', 'WAITING_APPROVAL' => 'MENUNGGU PERSETUJUAN',
            'SUBMITTED', 'SUBMISSION_QUEUED' => 'SEDANG DIPROSES',
            'CANCELLED' => 'DIBATALKAN',
            'COMPLETED' => 'SELESAI',
            'RELEASE_IN_PROGRESS' => 'PROSES PENGIRIMAN',
            'DRAFT' => 'DRAFT',
            'REVISION_REQUIRED' => 'PERLU REVISI',
            default => mb_strtoupper($strStatus, 'UTF-8'),
        };
    }

    private function translateRole(string $role): string
    {
        return match (trim($role)) {
            'Manager Department', 'Department Manager' => 'Manajer Departemen',
            'Plant Manager' => 'Manajer Pabrik',
            'VP Operations', 'VP Operasional' => 'VP Operasional',
            'Warehouse Manager' => 'Manajer Gudang',
            default => $role,
        };
    }

    private function translateApprovalStatus(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'APPROVED' => 'DISETUJUI',
            'REJECTED' => 'DITOLAK',
            'PENDING' => 'MENUNGGU PERSETUJUAN',
            'SUBMITTED' => 'DIAJUKAN',
            'CANCELLED' => 'DIBATALKAN',
            default => $status,
        };
    }

    private function logValidation(
        ?int $generationId,
        ?int $pageId,
        string $result,
        string $channel,
        string $uuid,
        ?string $ip,
        ?string $userAgent,
        ?string $sha256Token = null
    ): void {
        DocumentValidation::create([
            'uuid' => $uuid,
            'document_generation_id' => $generationId,
            'document_page_id' => $pageId,
            'validation_result' => $result,
            'verification_channel' => $channel,
            // SHA256 hash of the token looked up — enables audit trail without exposing raw token
            'lookup_fingerprint_sha256' => $sha256Token ? hash('sha256', $sha256Token) : null,
            // SHA256 of IP + UserAgent combined — request fingerprint for fraud detection
            'request_fingerprint_sha256' => ($ip && $userAgent) ? hash('sha256', $ip.'|'.$userAgent) : null,
            'ip_address_hash_sha256' => $ip ? hash('sha256', $ip) : null,
            'user_agent_hash_sha256' => $userAgent ? hash('sha256', $userAgent) : null,
            'correlation_id' => $uuid, // Use validation UUID as correlation ID
            'verified_at' => now(),
        ]);
    }

    /**
     * Safely format generated_at which may be a Carbon object or a Unix timestamp int
     * depending on the 'timestamp' Eloquent cast.
     */
    private function formatTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return Carbon::createFromTimestamp($value)->toIso8601String();
        }

        if (is_string($value)) {
            return Carbon::parse($value)->toIso8601String();
        }

        return $value->toIso8601String();
    }
}
