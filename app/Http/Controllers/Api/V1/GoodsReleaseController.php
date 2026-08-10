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
    public function index(Request $request): JsonResponse
    {
        $query = GoodsRelease::query()
            ->with(['sppbHeader.plant', 'sppbHeader.department', 'goodsReleaseItems.sppbDetail.item', 'goodsReleaseItems.sppbDetail.unit'])
            ->orderBy($request->query('sort', 'created_at'), $request->query('direction', 'desc'));

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('sppb_header_id')) {
            $query->where('sppb_header_id', $request->query('sppb_header_id'));
        }

        $releases = $query->paginate($request->query('per_page', 15));

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
            'driver_name' => 'nullable|string|max:100',
            'recipient_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'recipient_vehicle_number' => 'nullable|string|max:50',
            'expedition_name' => 'nullable|string|max:100',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $driverName = $request->input('driver_name') ?? $request->input('recipient_name') ?? 'Pengemudi';
        $vehicleNumber = $request->input('vehicle_number') ?? $request->input('recipient_vehicle_number') ?? '-';
        $expeditionName = $request->input('expedition_name') ?? 'Internal';
        $deliveryDate = $request->input('delivery_date') ?? now()->toDateString();

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
                    conditionOnRelease: 'Baik'
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
            driverName: $driverName,
            vehicleNumber: $vehicleNumber,
            expeditionName: $expeditionName,
            deliveryDate: $deliveryDate,
            notes: $request->input('notes')
        );

        try {
            $release = $goodsReleaseService->createGoodsRelease($dto);

            return response()->json([
                'success' => true,
                'message' => 'Surat jalan pelepasan barang berhasil dibuat.',
                'data' => $release->load(['sppbHeader', 'goodsReleaseItems.sppbDetail']),
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
        $release = $this->findGoodsRelease($uuid);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Detail pelepasan barang berhasil ditampilkan.',
            'data' => $release,
        ]);
    }

    /**
     * Confirm receipt of goods release (Surat Jalan DELIVERED).
     */
    public function receive(Request $request, string $uuid, GoodsReleaseService $goodsReleaseService): JsonResponse
    {
        $release = $this->findGoodsRelease($uuid);

        $request->validate([
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'receiving_notes' => 'nullable|string|max:1000',
            'recipient_name' => 'nullable|string|max:255',
            'received_by_name' => 'nullable|string|max:255',
            'recipient_signature' => 'nullable|string',
            'signature' => 'nullable|string',
            'received_by_id' => 'nullable|integer',
            'received_at' => 'nullable|date',
        ]);

        $updatedRelease = $goodsReleaseService->receiveGoodsRelease(
            $release,
            $request->all(),
            $request->user()?->id
        );

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => 'Surat Jalan berhasil dikonfirmasi diterima',
            'data' => [
                'id' => $updatedRelease->id,
                'uuid' => $updatedRelease->uuid,
                'release_number' => $updatedRelease->release_number,
                'status' => $updatedRelease->status,
                'notes' => $updatedRelease->notes,
                'recipient_name' => $updatedRelease->recipient_name,
                'recipient_signature' => $updatedRelease->recipient_signature,
                'receiving_notes' => $updatedRelease->receiving_notes,
                'received_at' => $updatedRelease->received_at?->toIso8601String(),
                'updated_at' => $updatedRelease->updated_at?->toIso8601String(),
            ],
        ]);
    }

    private function findGoodsRelease(string $idOrUuid): GoodsRelease
    {
        return GoodsRelease::query()
            ->where(function ($query) use ($idOrUuid) {
                $query->where('uuid', $idOrUuid)
                    ->orWhere('release_number', $idOrUuid)
                    ->orWhere('manual_release_number', $idOrUuid)
                    ->orWhere('verification_hash', $idOrUuid);
                if (is_numeric($idOrUuid)) {
                    $query->orWhere('id', (int) $idOrUuid);
                }
            })
            ->with([
                'sppbHeader.plant',
                'sppbHeader.department',
                'sppbHeader.requester',
                'sppbHeader.originLocation',
                'sppbHeader.destinationLocation',
                'goodsReleaseItems.sppbDetail.item',
                'goodsReleaseItems.sppbDetail.unit',
                'createdBy',
                'senderUser',
                'receiverUser',
            ])
            ->firstOrFail();
    }
}
