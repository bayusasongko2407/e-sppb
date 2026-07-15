<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentPage;
use App\Models\DocumentValidation;
use Illuminate\Support\Str;

class DocumentVerificationService
{
    /**
     * Verify a document page using its public UUID and page number.
     */
    public function verifyPublicPage(string $verificationUuid, int $pageNumber, ?string $ip = null, ?string $userAgent = null): array
    {
        $page = DocumentPage::with('documentGeneration.plant')->where('verification_uuid', $verificationUuid)->where('page_number', $pageNumber)->first();

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
                'generated_at' => $generation->generated_at?->toIso8601String(),
                'page_number' => $page->page_number,
                'total_pages' => $generation->page_count,
                'fingerprint' => substr($generation->checksum_sha256 ?? '', 0, 8),
            ],
        ];
    }

    private function logValidation(?int $generationId, ?int $pageId, string $result, string $channel, string $uuid, ?string $ip, ?string $userAgent): void
    {
        DocumentValidation::create([
            'uuid' => $uuid,
            'document_generation_id' => $generationId,
            'document_page_id' => $pageId,
            'validation_result' => $result,
            'verification_channel' => $channel,
            'ip_address_hash_sha256' => $ip ? hash('sha256', $ip) : null,
            'user_agent_hash_sha256' => $userAgent ? hash('sha256', $userAgent) : null,
            'verified_at' => now(),
        ]);
    }
}
