<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\DTOs\GoodsRelease\GoodsReleaseItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConfirmReceiptRequest;
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

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('release_number', 'like', '%'.$search.'%')
                    ->orWhere('manual_release_number', 'like', '%'.$search.'%')
                    ->orWhere('recipient_name', 'like', '%'.$search.'%')
                    ->orWhere('driver_name', 'like', '%'.$search.'%')
                    ->orWhere('vehicle_number', 'like', '%'.$search.'%')
                    ->orWhere('expedition_name', 'like', '%'.$search.'%')
                    ->orWhereHas('sppbHeader', function ($sq) use ($search) {
                        $sq->where('document_number', 'like', '%'.$search.'%');
                    });
            });
        }

        $perPage = (int) ($request->query('per_page') ?? $request->query('limit') ?? 15);
        $releases = $query->paginate($perPage);

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
     * Compatibility Route: Store a newly created Goods Release using sppb_header_id in payload.
     *
     * POST /api/v1/goods-releases
     */
    public function storeCompatibility(Request $request, GoodsReleaseService $goodsReleaseService): JsonResponse
    {
        $sppbHeaderId = $request->input('sppb_header_id');
        if (! $sppbHeaderId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter sppb_header_id wajib diisi.',
                'errors' => ['sppb_header_id' => ['Field sppb_header_id wajib diisi.']],
            ], 422);
        }

        $sppb = SppbHeader::query()
            ->where(function ($q) use ($sppbHeaderId) {
                $q->where('uuid', $sppbHeaderId)
                    ->orWhere('document_number', $sppbHeaderId);
                if (is_numeric($sppbHeaderId)) {
                    $q->orWhere('id', (int) $sppbHeaderId);
                }
            })
            ->first();

        if (! $sppb) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen SPPB tidak ditemukan.',
            ], 404);
        }

        return $this->store($request, $sppb->uuid, $goodsReleaseService);
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
     * Confirm receipt of goods — called from mobile app (authenticated or public via QR scan).
     *
     * POST /api/v1/goods-releases/{uuid}/receive
     * POST /api/v1/goods-releases/{uuid}/receive  (public, no auth required)
     *
     * Body:
     *   recipient_name      string  required  Nama penerima barang di lapangan
     *   recipient_signature string  optional  Tanda tangan base64 (data:image/png;base64,...)
     *   receiving_notes     string  optional  Catatan tambahan penerimaan
     *   received_at         date    optional  Waktu penerimaan (default: sekarang)
     */
    public function receive(ConfirmReceiptRequest $request, string $uuid, GoodsReleaseService $goodsReleaseService): JsonResponse
    {
        $release = $this->findGoodsRelease($uuid);

        $isAlreadyConfirmed = $release->received_at !== null
            && in_array($release->getRawOriginal('status'), ['DELIVERED', 'RECEIVED', 'COMPLETED']);

        try {
            $updatedRelease = $goodsReleaseService->receiveGoodsRelease(
                $release,
                $request->validated(),
                $request->user()?->id
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 413);
        }

        return response()->json([
            'success' => true,
            'message' => $isAlreadyConfirmed
                ? 'Surat Jalan ini sudah pernah dikonfirmasi sebelumnya.'
                : 'Penerimaan barang berhasil dikonfirmasi.',
            'data' => [
                'uuid' => $updatedRelease->uuid,
                'release_number' => $updatedRelease->manual_release_number ?? $updatedRelease->release_number,
                'status' => $updatedRelease->status,
                'recipient_name' => $updatedRelease->recipient_name,
                'has_signature' => ! empty($updatedRelease->recipient_signature),
                'recipient_signature' => $updatedRelease->recipient_signature,
                'receiving_notes' => $updatedRelease->receiving_notes,
                'received_at' => $updatedRelease->received_at?->toIso8601String(),
                'updated_at' => $updatedRelease->updated_at?->toIso8601String(),
            ],
            'already_confirmed' => $isAlreadyConfirmed,
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
