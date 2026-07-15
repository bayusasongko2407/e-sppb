<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\DocumentRendererInterface;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Services\DocumentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                ['is_official' => $generation->is_official]
            );

            // Calculate checksum
            $checksum = hash('sha256', $pdfContent);
            $fileSize = strlen($pdfContent);
            $pageCount = 1; // Simplification for now, a real PDF parser would count pages

            // Save to private storage
            $disk = 'private';
            $directory = 'documents/'.now()->format('Y/m');
            $storedName = $generation->uuid.'.pdf';
            $path = $directory.'/'.$storedName;

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

            // Generate Pages for QR verification
            for ($i = 1; $i <= $pageCount; $i++) {
                DocumentPage::create([
                    'document_generation_id' => $generation->id,
                    'verification_uuid' => Str::uuid()->toString(),
                    'page_number' => $i,
                    'page_checksum_sha256' => hash('sha256', $pdfContent.$i), // Simulated per-page checksum
                    'qr_payload_checksum_sha256' => hash('sha256', 'payload'.$i.$generation->uuid),
                    'verification_token_hash' => hash('sha256', Str::random(32)),
                ]);
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
