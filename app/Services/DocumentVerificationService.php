<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SppbStatus;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Models\DocumentValidation;
use App\Models\GoodsRelease;
use App\Models\SppbHeader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
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
     * Decrypt and extract payload from encrypted QR Code string or JSON payload structure.
     * Supports:
     * 1. Laravel Crypt Base64 encrypted string (Crypt::encryptString($payload))
     * 2. JSON structure { "iv": "...", "value": "...", "mac": "...", "tag": "" }
     * 3. Base64 encoded JSON structure { "iv": "...", "value": "...", "mac": "..." }
     * 4. Full Verification URL containing token or release number
     * 5. Plain text SHA256 token, Release Number, or SPPB document number
     */
    public function decryptQrPayload(mixed $rawInput): array
    {
        if (empty($rawInput)) {
            return [
                'decrypted' => null,
                'is_encrypted' => false,
                'original' => $rawInput,
                'decode_method' => 'EMPTY',
            ];
        }

        // Handle array payload format e.g. { "iv": "...", "value": "...", "mac": "..." }
        if (is_array($rawInput)) {
            if (isset($rawInput['iv'], $rawInput['value'], $rawInput['mac'])) {
                try {
                    $jsonStr = json_encode($rawInput);
                    $base64 = base64_encode($jsonStr);
                    $decrypted = Crypt::decryptString($base64);

                    return [
                        'decrypted' => $decrypted,
                        'is_encrypted' => true,
                        'original' => $rawInput,
                        'decode_method' => 'CRYPT_DECRYPT_ARRAY',
                    ];
                } catch (\Throwable $e) {
                    // Fallthrough to check nested keys
                }
            }

            $candidate = $rawInput['qr_data'] ?? $rawInput['encrypted_data'] ?? $rawInput['hash'] ?? $rawInput['token'] ?? null;
            if ($candidate && (is_string($candidate) || is_array($candidate))) {
                return $this->decryptQrPayload($candidate);
            }

            return [
                'decrypted' => null,
                'is_encrypted' => false,
                'original' => $rawInput,
                'decode_method' => 'UNKNOWN_ARRAY',
            ];
        }

        if (is_string($rawInput)) {
            $input = trim($rawInput);

            // Extract token/hash if full URL passed
            if (filter_var($input, FILTER_VALIDATE_URL) || str_contains($input, '/verify/document/')) {
                $path = parse_url($input, PHP_URL_PATH) ?? '';
                $segments = explode('/', trim($path, '/'));
                $lastSegment = end($segments);
                if ($lastSegment) {
                    $input = urldecode($lastSegment);
                }
            }

            // Direct Crypt::decryptString
            try {
                $decrypted = Crypt::decryptString($input);

                return [
                    'decrypted' => $decrypted,
                    'is_encrypted' => true,
                    'original' => $rawInput,
                    'decode_method' => 'CRYPT_DECRYPT_STRING',
                ];
            } catch (\Throwable $e) {
                // Not standard direct Crypt::decryptString
            }

            // Stringified JSON payload { "iv": "...", "value": "...", "mac": "..." }
            if (str_starts_with($input, '{') && str_ends_with($input, '}')) {
                $decodedJson = json_decode($input, true);
                if (is_array($decodedJson) && isset($decodedJson['iv'], $decodedJson['value'], $decodedJson['mac'])) {
                    try {
                        $base64 = base64_encode($input);
                        $decrypted = Crypt::decryptString($base64);

                        return [
                            'decrypted' => $decrypted,
                            'is_encrypted' => true,
                            'original' => $rawInput,
                            'decode_method' => 'CRYPT_DECRYPT_JSON_STRING',
                        ];
                    } catch (\Throwable $e) {
                        //
                    }
                }
            }

            // Base64 encoded JSON string
            $decodedBase64 = base64_decode($input, true);
            if ($decodedBase64 !== false && str_starts_with(trim($decodedBase64), '{')) {
                $jsonArray = json_decode(trim($decodedBase64), true);
                if (is_array($jsonArray) && isset($jsonArray['iv'], $jsonArray['value'], $jsonArray['mac'])) {
                    try {
                        $decrypted = Crypt::decryptString($input);

                        return [
                            'decrypted' => $decrypted,
                            'is_encrypted' => true,
                            'original' => $rawInput,
                            'decode_method' => 'CRYPT_DECRYPT_BASE64_JSON',
                        ];
                    } catch (\Throwable $e) {
                        //
                    }
                }
            }

            // Plain text string (Release Number, SHA256 Token, SPPB Number, UUID)
            return [
                'decrypted' => $input,
                'is_encrypted' => false,
                'original' => $rawInput,
                'decode_method' => preg_match('/^[a-f0-9]{64}$/i', $input) ? 'SHA256_HEX' : 'RAW_STRING',
            ];
        }

        return [
            'decrypted' => null,
            'is_encrypted' => false,
            'original' => $rawInput,
            'decode_method' => 'INVALID_INPUT',
        ];
    }

    /**
     * Unified document & Goods Release (Resi Surat Jalan) verification handler.
     * Decrypts encrypted QR payloads and looks up GoodsRelease, DocumentPage, or SppbHeader.
     */
    public function verifyDocument(mixed $rawInput, ?string $ip = null, ?string $userAgent = null, ?User $actor = null): array
    {
        $validationId = Str::uuid()->toString();
        $decoded = $this->decryptQrPayload($rawInput);
        $target = $decoded['decrypted'];

        if (empty($target)) {
            $this->logValidation(null, null, 'NOT_FOUND', 'API_QR', $validationId, $ip, $userAgent, is_string($rawInput) ? $rawInput : json_encode($rawInput));

            return [
                'status' => 'NOT_FOUND',
                'message' => 'Payload QR Code atau token verifikasi tidak valid.',
                'validation_id' => $validationId,
                'data' => null,
            ];
        }

        // 1. If 64-character SHA256 hex string, test DocumentPage SHA256 token verification first
        if (preg_match('/^[a-f0-9]{64}$/i', $target)) {
            $pageResult = $this->verifyBySha256Token($target, $ip, $userAgent);
            if ($pageResult['status'] !== 'NOT_FOUND') {
                if (is_array($pageResult['data'])) {
                    $pageResult['data']['decrypted_from_qr'] = $decoded['is_encrypted'];
                }

                return $pageResult;
            }
        }

        // 2. Search GoodsRelease (Resi Surat Jalan)
        $goodsRelease = GoodsRelease::with([
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'sppbHeader.originLocation',
            'sppbHeader.destinationLocation',
            'sppbHeaders.plant',
            'sppbHeaders.department',
            'sppbHeaders.requester',
            'goodsReleaseItems.sppbDetail.item',
            'goodsReleaseItems.sppbDetail.asset',
            'goodsReleaseItems.sppbDetail.unit',
            'createdBy',
            'senderUser',
            'receiverUser',
        ])
            ->where('release_number', $target)
            ->orWhere('manual_release_number', $target)
            ->orWhere('verification_hash', $target)
            ->orWhere('uuid', $target)
            ->first();

        if ($goodsRelease) {
            $status = match ($goodsRelease->status) {
                'CANCELLED' => 'CANCELLED',
                default => 'VALID',
            };

            $this->logValidation(null, null, $status, 'API_QR', $validationId, $ip, $userAgent, $target);

            return [
                'status' => $status,
                'validation_id' => $validationId,
                'data' => $this->buildGoodsReleaseDetails($goodsRelease, $decoded['is_encrypted']),
            ];
        }

        // 3. Search SppbHeader directly
        $sppb = SppbHeader::with([
            'plant',
            'department',
            'requester',
            'originLocation',
            'destinationLocation',
            'sppbDetails.item',
            'sppbDetails.unit',
            'currentWorkflowInstance.workflowInstanceSteps',
            'workflowInstances.workflowInstanceSteps',
        ])
            ->where('document_number', $target)
            ->orWhere('uuid', $target)
            ->first();

        if ($sppb) {
            $status = match ($sppb->status?->value ?? (string) $sppb->status) {
                'CANCELLED' => 'CANCELLED',
                'REJECTED' => 'REJECTED',
                default => 'VALID',
            };

            $this->logValidation(null, null, $status, 'API_QR', $validationId, $ip, $userAgent, $target);

            return [
                'status' => $status,
                'validation_id' => $validationId,
                'data' => $this->buildSppbDetails($sppb, $decoded['is_encrypted']),
            ];
        }

        // 4. Not found
        $this->logValidation(null, null, 'NOT_FOUND', 'API_QR', $validationId, $ip, $userAgent, $target);

        return [
            'status' => 'NOT_FOUND',
            'message' => 'Dokumen atau Surat Jalan tidak ditemukan.',
            'validation_id' => $validationId,
            'data' => null,
        ];
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

    private function buildGoodsReleaseDetails(GoodsRelease $goodsRelease, bool $isEncrypted): array
    {
        $goodsRelease->loadMissing([
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'sppbHeader.originLocation',
            'sppbHeader.destinationLocation',
            'sppbHeaders.plant',
            'sppbHeaders.department',
            'sppbHeaders.requester',
            'goodsReleaseItems.sppbDetail.item',
            'goodsReleaseItems.sppbDetail.asset',
            'goodsReleaseItems.sppbDetail.unit',
            'createdBy',
            'senderUser',
            'receiverUser',
        ]);

        $sppbHeader = $goodsRelease->sppbHeader ?? $goodsRelease->sppbHeaders->first();

        $statusDisplay = match ($goodsRelease->status) {
            'DRAFT' => 'DRAFT',
            'RELEASED' => 'DALAM PENGIRIMAN',
            'RECEIVED' => 'TERKIRIM',
            'CANCELLED' => 'DIBATALKAN',
            default => $goodsRelease->status,
        };

        return [
            'document_type' => 'SURAT_JALAN',
            'document_number' => $goodsRelease->is_manual && $goodsRelease->manual_release_number
                ? $goodsRelease->manual_release_number
                : $goodsRelease->release_number,
            'release_number' => $goodsRelease->release_number,
            'manual_release_number' => $goodsRelease->manual_release_number,
            'status' => $goodsRelease->status,
            'status_display' => $statusDisplay,
            'is_manual' => (bool) $goodsRelease->is_manual,
            'delivery_date' => $goodsRelease->delivery_date
                ? (is_string($goodsRelease->delivery_date)
                    ? $goodsRelease->delivery_date
                    : $goodsRelease->delivery_date->format('Y-m-d'))
                : null,
            'plant_name' => $sppbHeader?->plant?->name ?? '—',
            'department_name' => $sppbHeader?->department?->name ?? '—',
            'department' => $sppbHeader?->department ? [
                'id' => $sppbHeader->department->id,
                'code' => $sppbHeader->department->code ?? '',
                'name' => $sppbHeader->department->name,
            ] : null,
            'requester_name' => $sppbHeader?->requester?->name ?? $goodsRelease->createdBy?->name ?? '—',
            'needed_name' => $sppbHeader?->needed_name ?? $sppbHeader?->requester?->name ?? $goodsRelease->createdBy?->name ?? '—',
            'requester' => $sppbHeader?->requester ? [
                'id' => $sppbHeader->requester->id,
                'name' => $sppbHeader->requester->name,
                'nik' => $sppbHeader->requester->nik ?? '',
            ] : ($goodsRelease->createdBy ? [
                'id' => $goodsRelease->createdBy->id,
                'name' => $goodsRelease->createdBy->name,
                'nik' => $goodsRelease->createdBy->nik ?? '',
            ] : null),
            'destination_location_name' => $goodsRelease->receiver_name ?? $sppbHeader?->destinationLocation?->name ?? '—',
            'destination_name' => $goodsRelease->receiver_name ?? $sppbHeader?->destinationLocation?->name ?? '—',
            'destination_location' => $sppbHeader?->destinationLocation ? [
                'id' => $sppbHeader->destinationLocation->id,
                'code' => $sppbHeader->destinationLocation->code ?? '',
                'name' => $sppbHeader->destinationLocation->name,
            ] : null,
            'created_by' => $goodsRelease->createdBy?->name ?? '—',
            'driver_name' => $goodsRelease->driver_name ?? '—',
            'vehicle_number' => $goodsRelease->vehicle_number ?? '—',
            'expedition_name' => $goodsRelease->expedition_name ?? '—',
            'locations' => [
                'origin' => $goodsRelease->sender_name ?? $sppbHeader?->originLocation?->name ?? '—',
                'origin_address' => $goodsRelease->sender_address ?? '—',
                'destination' => $goodsRelease->receiver_name ?? $sppbHeader?->destinationLocation?->name ?? '—',
                'destination_address' => $goodsRelease->receiver_address ?? '—',
            ],
            'items_summary' => [
                'total_item_types' => $goodsRelease->goodsReleaseItems->count(),
                'total_quantity_released' => (float) $goodsRelease->goodsReleaseItems->sum('quantity_released'),
            ],
            'items' => $goodsRelease->goodsReleaseItems->map(function ($item) {
                return [
                    'item_name' => $item->sppbDetail?->item_asset_name ?? '—',
                    'barcode' => $item->sppbDetail?->asset?->barcode ?? $item->sppbDetail?->item?->code ?? $item->sppbDetail?->reference_code ?? '—',
                    'quantity_requested' => (float) $item->quantity_requested,
                    'quantity_released' => (float) $item->quantity_released,
                    'unit' => $item->sppbDetail?->unit?->name ?? '—',
                    'condition' => $item->condition_on_release ?? '—',
                ];
            })->values()->all(),
            'sppb_references' => $goodsRelease->sppbHeaders->map(function ($sppb) {
                return [
                    'document_number' => $sppb->document_number,
                    'request_date' => $sppb->request_date
                        ? (is_string($sppb->request_date) ? $sppb->request_date : $sppb->request_date->format('Y-m-d'))
                        : null,
                    'requester_name' => $sppb->requester?->name,
                    'status' => $sppb->status,
                ];
            })->values()->all(),
            'notes' => $goodsRelease->notes,
            'recipient_name' => $goodsRelease->recipient_name ?? $goodsRelease->receiver_name,
            'recipient_signature' => $goodsRelease->recipient_signature,
            'receiving_notes' => $goodsRelease->receiving_notes,
            'verification_hash' => $goodsRelease->verification_hash,
            'decrypted_from_qr' => $isEncrypted,
            'verified_at' => now()->toIso8601String(),
        ];
    }

    private function buildSppbDetails(SppbHeader $sppb, bool $isEncrypted): array
    {
        $totalTypes = $sppb->sppbDetails->count();
        $totalQtyApproved = (int) $sppb->sppbDetails->sum('quantity');

        $requestDate = $sppb->request_date
            ? (is_string($sppb->request_date) ? $sppb->request_date : $sppb->request_date->format('Y-m-d'))
            : null;

        $dateNeeded = $sppb->date_needed
            ? (is_string($sppb->date_needed) ? $sppb->date_needed : $sppb->date_needed->format('Y-m-d'))
            : null;

        $requesterName = $sppb->requester?->name ?? $sppb->needed_name ?? '—';
        $destinationName = $sppb->destinationLocation?->name ?? '—';
        $departmentName = $sppb->department?->name ?? '—';

        return [
            'document_type' => 'SPPB',
            'document_number' => $sppb->document_number,
            'status_sppb' => $this->translateSppbStatus($sppb->status),
            'plant_name' => $sppb->plant?->name ?? '—',
            'department_name' => $departmentName,
            'department' => $sppb->department ? [
                'id' => $sppb->department->id,
                'code' => $sppb->department->code ?? '',
                'name' => $sppb->department->name,
            ] : null,
            'requester_name' => $requesterName,
            'needed_name' => $sppb->needed_name ?? $requesterName,
            'requester' => $sppb->requester ? [
                'id' => $sppb->requester->id,
                'name' => $sppb->requester->name,
                'nik' => $sppb->requester->nik ?? '',
            ] : null,
            'destination_location_name' => $destinationName,
            'destination_name' => $destinationName,
            'destination_location' => $sppb->destinationLocation ? [
                'id' => $sppb->destinationLocation->id,
                'code' => $sppb->destinationLocation->code ?? '',
                'name' => $sppb->destinationLocation->name,
            ] : null,
            'is_urgent' => (bool) $sppb->is_urgent,
            'request_date' => $requestDate,
            'date_needed' => $dateNeeded,
            'purpose' => $sppb->purpose ?? $sppb->needed_name ?? '—',
            'locations' => [
                'origin' => $sppb->originLocation?->name ?? '—',
                'destination' => $destinationName,
            ],
            'items_summary' => [
                'total_item_types' => $totalTypes,
                'total_quantity_approved' => $totalQtyApproved,
            ],
            'approval_summary' => $this->resolveApprovalSummary($sppb, []),
            'decrypted_from_qr' => $isEncrypted,
            'verified_at' => now()->toIso8601String(),
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
            'lookup_fingerprint_sha256' => $sha256Token ? hash('sha256', (string) $sha256Token) : null,
            'request_fingerprint_sha256' => ($ip && $userAgent) ? hash('sha256', $ip.'|'.$userAgent) : null,
            'ip_address_hash_sha256' => $ip ? hash('sha256', $ip) : null,
            'user_agent_hash_sha256' => $userAgent ? hash('sha256', $userAgent) : null,
            'correlation_id' => $uuid,
            'verified_at' => now(),
        ]);
    }

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
