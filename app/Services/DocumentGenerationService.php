<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Document\DocumentGenerationData;
use App\Models\DocumentGeneration;
use App\Models\DocumentTemplate;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentGenerationService
{
    /**
     * Request a document generation asynchronously.
     */
    public function requestGeneration(DocumentGenerationData $data): DocumentGeneration
    {
        return DB::transaction(function () use ($data) {
            $template = DocumentTemplate::findOrFail($data->templateId);
            $plant = Plant::findOrFail($data->plantId);

            $commandUuid = $data->resolveCommandUuid();

            // Calculate generation_no
            $generationNo = 1;
            if ($data->supersedesId) {
                $superseded = DocumentGeneration::findOrFail($data->supersedesId);
                $generationNo = $superseded->generation_no + 1;
            } else {
                $generationNo = DocumentGeneration::where('document_type', $data->documentType)
                    ->where(function ($q) use ($data) {
                        if ($data->sppbHeaderId) {
                            $q->where('sppb_header_id', $data->sppbHeaderId);
                        }
                        if ($data->goodsReleaseId) {
                            $q->where('goods_release_id', $data->goodsReleaseId);
                        }
                    })->max('generation_no') + 1;
            }

            // Document Number mapping
            $documentNumber = 'DOC-'.time(); // Fallback
            if ($data->sppbHeaderId) {
                $documentNumber = SppbHeader::find($data->sppbHeaderId)?->sppb_no ?? $documentNumber;
            } elseif ($data->goodsReleaseId) {
                $documentNumber = GoodsRelease::find($data->goodsReleaseId)?->sj_no ?? $documentNumber;
            }

            $sourceChecksum = hash('sha256', json_encode($data->renderPayload));

            $generation = DocumentGeneration::create([
                'uuid' => Str::uuid()->toString(),
                'command_uuid' => $commandUuid,
                'document_template_id' => $template->id,
                'template_version' => $template->version,
                'plant_id' => $plant->id,
                'sppb_header_id' => $data->sppbHeaderId,
                'goods_release_id' => $data->goodsReleaseId,
                'document_type' => $data->documentType,
                'document_number' => $documentNumber,
                'source_revision_no' => 0, // Should be passed if tracking source revisions
                'generation_no' => $generationNo,
                'supersedes_id' => $data->supersedesId,
                'generated_by_id' => $data->generatedById,
                'status' => 'QUEUED',
                'is_official' => $data->isOfficial,
                'plant_code_snapshot' => $plant->code,
                'plant_name_snapshot' => $plant->name,
                'render_payload' => $data->renderPayload,
                'source_checksum_sha256' => $sourceChecksum,
            ]);

            // Dispatch Job (to be implemented)
            // ProcessDocumentGenerationJob::dispatch($generation->id);

            return $generation;
        });
    }

    /**
     * Complete the document generation with actual file details.
     */
    public function completeGeneration(DocumentGeneration $generation, string $disk, string $path, string $storedName, int $fileSize, string $checksum, int $pageCount): void
    {
        $generation->update([
            'status' => 'READY',
            'disk' => $disk,
            'path' => $path,
            'directory' => dirname($path),
            'stored_name' => $storedName,
            'mime_type' => 'application/pdf',
            'file_size' => $fileSize,
            'checksum_sha256' => $checksum,
            'page_count' => $pageCount,
            'generated_at' => now(),
        ]);

        // If supersedes, set superseded status
        if ($generation->supersedes_id) {
            DocumentGeneration::where('id', $generation->supersedes_id)->update([
                'status' => 'SUPERSEDED',
                'revoked_at' => now(),
                'revocation_reason' => 'Superseded by generation '.$generation->generation_no,
                'revoked_by_id' => $generation->generated_by_id,
            ]);
        }
    }

    /**
     * Mark a generation as failed.
     */
    public function failGeneration(DocumentGeneration $generation, string $errorCode, string $errorMessage): void
    {
        $generation->update([
            'status' => 'FAILED',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }
}
