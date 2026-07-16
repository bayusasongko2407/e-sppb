<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\DocumentRendererInterface;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Services\DocumentGenerationService;
use App\Services\DocumentVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public int $documentGenerationId
    ) {
        $this->onQueue('documents');
    }

    public function handle(
        DocumentGenerationService $documentGenerationService,
        DocumentRendererInterface $renderer
    ): void {
        $generation = DocumentGeneration::find($this->documentGenerationId);

        if (! $generation || $generation->status !== 'QUEUED') {
            return;
        }

        $generation->update(['status' => 'PROCESSING', 'processing_started_at' => now()]);

        try {
            // Render PDF
            $pdfContent = $renderer->renderToPdf(
                $generation->documentTemplate->template_path ?? 'default',
                $generation->render_payload,
                [
                    'is_official' => $generation->is_official,
                    'generation_uuid' => $generation->uuid,
                ]
            );

            // Calculate checksum
            $checksum = hash('sha256', $pdfContent);
            $fileSize = strlen($pdfContent);
            $pageCount = 1; // Simplification; a real PDF parser would count pages

            // Save to private storage — ensure parent directory exists
            $disk = 'private';
            $directory = 'documents/'.now()->format('Y/m');
            $storedName = $generation->uuid.'.pdf';
            $path = $directory.'/'.$storedName;

            // Create directory if it does not exist (Storage::put does not auto-create)
            if (! Storage::disk($disk)->exists($directory)) {
                Storage::disk($disk)->makeDirectory($directory);
            }

            Storage::disk($disk)->put($path, $pdfContent);

            // Complete Generation
            $documentGenerationService->completeGeneration(
                $generation,
                $disk,
                $path,
                $storedName,
                $fileSize,
                $checksum,
                $pageCount
            );

            // Generate Pages for QR verification using SHA256 tokens
            for ($i = 1; $i <= $pageCount; $i++) {
                $sha256Token = DocumentVerificationService::deriveToken($generation->uuid, $i);

                // Deterministic UUID derived from generation UUID + page (for backwards compat)
                $hash = md5($generation->uuid.'-'.$i);
                $verificationUuid = sprintf('%08s-%04s-%04s-%04s-%12s',
                    substr($hash, 0, 8),
                    substr($hash, 8, 4),
                    substr($hash, 12, 4),
                    substr($hash, 16, 4),
                    substr($hash, 20, 12)
                );

                DocumentPage::updateOrCreate(
                    [
                        'document_generation_id' => $generation->id,
                        'page_number' => $i,
                    ],
                    [
                        'verification_uuid' => $verificationUuid,
                        'page_checksum_sha256' => hash('sha256', $pdfContent.$i),
                        'qr_payload_checksum_sha256' => hash('sha256', 'payload'.$i.$generation->uuid),
                        'verification_token_hash' => $sha256Token,
                    ]
                );
            }

        } catch (\Throwable $e) {
            $documentGenerationService->failGeneration(
                $generation,
                'RENDER_ERROR',
                $e->getMessage()
            );

            throw $e;
        }
    }
}
