<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentPage;
use App\Models\DocumentValidation;
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
        $page = DocumentPage::with('documentGeneration.plant')
            ->where('verification_token_hash', $sha256Token)
            ->first();

        $validationId = Str::uuid()->toString();

        if (! $page) {
            $this->logValidation(null, null, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent, $sha256Token);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $generation = $page->documentGeneration;

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
            'data' => [
                'document_type' => $generation->document_type,
                'document_number' => $generation->document_number,
                'plant_code' => $generation->plant_code_snapshot,
                'plant_name' => $generation->plant_name_snapshot,
                'generated_at' => $this->formatTimestamp($generation->generated_at),
                'page_number' => $page->page_number,
                'total_pages' => $generation->page_count,
                'fingerprint' => substr($generation->checksum_sha256 ?? '', 0, 16),
            ],
        ];
    }

    /**
     * @deprecated Use verifyBySha256Token() instead.
     * Kept for backwards compatibility with older QR codes generated before SHA256 migration.
     */
    public function verifyPublicPage(string $verificationUuid, int $pageNumber, ?string $ip = null, ?string $userAgent = null): array
    {
        $page = DocumentPage::with('documentGeneration.plant')
            ->where('verification_uuid', $verificationUuid)
            ->where('page_number', $pageNumber)
            ->first();

        $validationId = Str::uuid()->toString();

        if (! $page) {
            $this->logValidation(null, null, 'NOT_FOUND', 'PUBLIC_QR', $validationId, $ip, $userAgent);

            return ['status' => 'NOT_FOUND', 'data' => null, 'validation_id' => $validationId];
        }

        $generation = $page->documentGeneration;

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
            'data' => [
                'document_type' => $generation->document_type,
                'document_number' => $generation->document_number,
                'plant_code' => $generation->plant_code_snapshot,
                'plant_name' => $generation->plant_name_snapshot,
                'generated_at' => $this->formatTimestamp($generation->generated_at),
                'page_number' => $page->page_number,
                'total_pages' => $generation->page_count,
                'fingerprint' => substr($generation->checksum_sha256 ?? '', 0, 16),
            ],
        ];
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

        return $value->toIso8601String();
    }
}
