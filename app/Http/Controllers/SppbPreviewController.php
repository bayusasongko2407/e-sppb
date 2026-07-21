<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\DocumentRendererInterface;
use App\DTOs\Document\DocumentGenerationData;
use App\Jobs\ProcessDocumentGenerationJob;
use App\Models\DocumentTemplate;
use App\Models\SppbHeader;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SppbPreviewController extends Controller
{
    public function preview(SppbHeader $record, DocumentGenerationService $generationService, DocumentRendererInterface $renderer)
    {
        $sppbHeader = $record->loadMissing(['sppbDetails.unit', 'plant', 'department', 'requester']);

        Gate::authorize('view', $sppbHeader);

        // Find or create fallback template
        $template = DocumentTemplate::where('plant_id', $sppbHeader->plant_id)->first()
            ?? DocumentTemplate::first();

        if (! $template) {
            $template = DocumentTemplate::create([
                'uuid' => (string) Str::uuid(),
                'plant_id' => $sppbHeader->plant_id,
                'name' => 'Default SPPB Template',
                'code' => 'SPPB-DEFAULT',
                'version' => 1,
                'template_path' => 'default',
                'is_active' => true,
                'document_type' => 'sppb',
                'renderer' => 'dummy',
                'template_checksum_sha256' => hash('sha256', 'default'),
                'configuration' => '{}',
                'created_by_id' => auth()->id(),
            ]);
        }

        // Build payload
        $payload = [
            'sppb_no' => $sppbHeader->sppb_no, // Uses the model accessor mapping
            'request_date' => $sppbHeader->request_date,
            'requester' => $sppbHeader->requester?->name,
            'plant' => $sppbHeader->plant?->name,
            'department' => $sppbHeader->department?->name,
            'details' => $sppbHeader->sppbDetails->map(fn ($detail) => [
                'item_name' => $detail->item_asset_name,
                'quantity' => $detail->quantity,
                'unit' => $detail->unit?->name,
                'remarks' => $detail->remarks,
            ])->toArray(),
        ];

        $generation = $generationService->requestGeneration(
            new DocumentGenerationData(
                documentType: 'sppb',
                templateId: $template->id,
                plantId: $sppbHeader->plant_id,
                generatedById: auth()->id(),
                renderPayload: $payload,
                sppbHeaderId: $sppbHeader->id,
            )
        );

        // Execute generation synchronously
        $job = new ProcessDocumentGenerationJob($generation->id);
        $job->handle($generationService, $renderer);

        $generation->refresh();

        if ($generation->status === 'READY') {
            // As per request: "gunakan model preview/diarahkan ke link new tab saat klik cetak pdf"
            // Return the structured HTML view that works beautifully with browser Print to PDF
            return view('sppb.preview', ['header' => $sppbHeader]);
        }

        abort(500, 'Gagal memproses pratinjau PDF.');
    }
}
