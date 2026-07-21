<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\DTOs\GoodsRelease\GoodsReleaseItemData;
use App\Http\Controllers\Controller;
use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\SppbHeader;
use App\Services\GoodsReleaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsReleaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $releases = GoodsRelease::with(['sppbHeader', 'items.sppbDetail.item'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelepasan barang berhasil ditampilkan.',
            'data' => $releases->items(),
            'meta' => [
                'current_page' => $releases->currentPage(),
                'per_page' => $releases->perPage(),
                'total' => $releases->total(),
                'last_page' => $releases->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $uuid, GoodsReleaseService $goodsReleaseService): JsonResponse
    {
        $sppb = SppbHeader::where('uuid', $uuid)->with('sppbDetails')->firstOrFail();

        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Auto-release all remaining quantities for the SPPB details
        $items = [];
        foreach ($sppb->sppbDetails as $detail) {
            $alreadyReleased = GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                ->sum('quantity_released');
            $remaining = (float) $detail->quantity - (float) $alreadyReleased;

            if ($remaining > 0) {
                $items[] = new GoodsReleaseItemData(
                    sppbDetailId: (int) $detail->id,
                    quantityReleased: $remaining,
                    conditionOnRelease: 'GOOD'
                );
            }
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Semua barang dalam SPPB ini sudah dilepaskan.',
            ], 422);
        }

        $dto = new CreateGoodsReleaseData(
            sppbHeaderId: (int) $sppb->id,
            actorId: (int) $request->user()->id,
            items: $items,
            driverName: $request->input('recipient_name'),
            vehicleNumber: $request->input('recipient_vehicle_number'),
            deliveryDate: now()->toDateString(),
            notes: $request->input('notes')
        );

        try {
            $release = $goodsReleaseService->createGoodsRelease($dto);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dilepaskan.',
                'data' => $release,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal merilis barang: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $release = GoodsRelease::where('uuid', $uuid)
            ->with(['sppbHeader.plant', 'items.sppbDetail.item'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail pelepasan barang berhasil ditampilkan.',
            'data' => $release,
        ]);
    }
}
