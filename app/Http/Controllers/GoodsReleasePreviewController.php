<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\DocumentRendererInterface;
use App\DTOs\Document\DocumentGenerationData;
use App\Jobs\ProcessDocumentGenerationJob;
use App\Models\DocumentTemplate;
use App\Models\GoodsRelease;
use App\Services\DocumentGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class GoodsReleasePreviewController extends Controller
{
    /**
     * Preview the Goods Release document as a printable HTML/PDF.
     */
    public function preview(
        GoodsRelease $record,
        DocumentGenerationService $generationService,
        DocumentRendererInterface $renderer
    ): View {
        $goodsRelease = $record->loadMissing([
            'goodsReleaseItems.sppbDetail.unit',
            'goodsReleaseItems.sppbDetail.item',
            'goodsReleaseItems.sppbDetail.asset',
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'sppbHeaders.requester',
        ]);

        Gate::authorize('view', $goodsRelease);

        $plantId = $goodsRelease->sppbHeader?->plant_id
            ?? ($goodsRelease->sppbHeaders->first()?->plant_id ?? 0);

        // Find or create fallback template for goods_release
        $template = DocumentTemplate::where('document_type', 'goods_release')
            ->where('plant_id', $plantId)
            ->first()
            ?? DocumentTemplate::where('document_type', 'goods_release')->first();

        if (! $template) {
            $template = DocumentTemplate::create([
                'uuid' => (string) Str::uuid(),
                'plant_id' => $plantId ?: null,
                'name' => 'Default Goods Release Template',
                'code' => 'GR-DEFAULT',
                'version' => 1,
                'template_path' => 'default',
                'is_active' => true,
                'document_type' => 'goods_release',
                'renderer' => 'dummy',
                'template_checksum_sha256' => hash('sha256', 'default'),
                'configuration' => '{}',
                'created_by_id' => auth()->id(),
            ]);
        }

        // Build payload
        $payload = [
            'release_number' => $goodsRelease->is_manual ? $goodsRelease->manual_release_number : $goodsRelease->release_number,
            'is_manual' => $goodsRelease->is_manual,
            'reference_number' => $goodsRelease->release_number,
            'delivery_date' => $goodsRelease->delivery_date?->toDateString(),
            'driver_name' => $goodsRelease->driver_name,
            'vehicle_number' => $goodsRelease->vehicle_number,
            'expedition_name' => $goodsRelease->expedition_name,
            'sender_name' => $goodsRelease->sender_name,
            'sender_address' => $goodsRelease->sender_address,
            'receiver_name' => $goodsRelease->receiver_name,
            'receiver_address' => $goodsRelease->receiver_address,
            'status' => $goodsRelease->status,
            'notes' => $goodsRelease->notes,
            'details' => $goodsRelease->goodsReleaseItems->map(fn ($item) => [
                'item_name' => $item->sppbDetail?->item_asset_name,
                'barcode' => $item->sppbDetail?->asset?->barcode ?? $item->sppbDetail?->item?->code ?? $item->sppbDetail?->reference_code ?? '-',
                'quantity_requested' => $item->quantity_requested,
                'quantity_released' => $item->quantity_released,
                'unit' => $item->sppbDetail?->unit?->name,
                'condition' => $item->condition_on_release,
            ])->toArray(),
        ];

        $generation = $generationService->requestGeneration(
            new DocumentGenerationData(
                documentType: 'goods_release',
                templateId: $template->id,
                plantId: (int) $plantId,
                generatedById: (int) auth()->id(),
                renderPayload: $payload,
                goodsReleaseId: $goodsRelease->id,
            )
        );

        // Execute generation synchronously
        $job = new ProcessDocumentGenerationJob($generation->id);
        $job->handle($generationService, $renderer);

        $generation->refresh();

        if ($generation->status === 'READY') {
            return view('goods-releases.preview', [
                'record' => $goodsRelease,
            ]);
        }

        abort(500, 'Gagal memproses pratinjau PDF.');
    }
}
