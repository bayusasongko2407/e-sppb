<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\Enums\SppbStatus;
use App\Exceptions\GoodsRelease\InvalidGoodsReleaseQuantityException;
use App\Exceptions\Workflow\InvalidSppbTransitionException;
use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\SppbHeader;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GoodsReleaseService
{
    public function createGoodsRelease(CreateGoodsReleaseData $data): GoodsRelease
    {
        return DB::transaction(function () use ($data) {
            $sppb = SppbHeader::with([
                'sppbDetails' => function ($q) {
                    $q->lockForUpdate();
                },
                'originLocation',
                'destinationLocation',
            ])->lockForUpdate()->findOrFail($data->sppbHeaderId);

            if (! in_array(SppbStatus::from($sppb->status), [SppbStatus::APPROVED, SppbStatus::RELEASE_IN_PROGRESS])) {
                throw new InvalidSppbTransitionException($sppb->status, 'RELEASE_IN_PROGRESS');
            }

            // Determine Release Sequence
            $currentReleases = GoodsRelease::where('sppb_header_id', $sppb->id)->count();
            $sequence = $currentReleases + 1;

            $releaseNumber = 'SJ-'.now()->format('Ymd').'-'.str_pad((string) $sppb->id, 4, '0', STR_PAD_LEFT).'-'.$sequence;

            $release = GoodsRelease::create([
                'uuid' => (string) Uuid::uuid4(),
                'release_number' => $releaseNumber,
                'sppb_header_id' => $sppb->id,
                'release_sequence' => $sequence,
                'is_manual' => false,
                'created_by_id' => $data->actorId,
                'sender_name' => $sppb->originLocation?->name ?? 'Unknown',
                'sender_address' => $sppb->originLocation?->address ?? '-',
                'receiver_name' => $sppb->destinationLocation?->name ?? 'Unknown',
                'receiver_address' => $sppb->destinationLocation?->address ?? '-',
                'sender_user_id' => $data->actorId, // Or fetched from context
                'driver_name' => $data->driverName,
                'vehicle_number' => $data->vehicleNumber,
                'expedition_name' => $data->expeditionName,
                'delivery_date' => $data->deliveryDate,
                'status' => 'PENDING',
                'notes' => $data->notes,
                'verification_hash' => hash('sha256', $releaseNumber.uniqid('', true)),
            ]);

            $isAllCompleted = true;

            foreach ($data->items as $itemData) {
                if ($itemData->quantityReleased <= 0) {
                    continue;
                }

                $detail = $sppb->sppbDetails->firstWhere('id', $itemData->sppbDetailId);
                if (! $detail) {
                    throw new \InvalidArgumentException('SPPB detail tidak ditemukan dalam SPPB header ini.');
                }

                // Kalkulasi kuantitas yang sudah dikeluarkan sebelumnya
                $alreadyReleased = GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                    ->sum('quantity_released');

                $remaining = $detail->quantity - $alreadyReleased;

                if ($itemData->quantityReleased > $remaining) {
                    throw new InvalidGoodsReleaseQuantityException;
                }

                GoodsReleaseItem::create([
                    'goods_release_id' => $release->id,
                    'sppb_detail_id' => $detail->id,
                    'quantity_requested' => $detail->quantity,
                    'quantity_released' => $itemData->quantityReleased,
                    'quantity_received' => 0,
                    'condition_on_release' => $itemData->conditionOnRelease,
                    'is_checked' => true,
                    'notes' => $itemData->notes,
                ]);

                if (($alreadyReleased + $itemData->quantityReleased) < $detail->quantity) {
                    $isAllCompleted = false;
                }
            }

            // Jika semua barang sudah dikeluarkan/dirilis
            $sppb->status = $isAllCompleted ? SppbStatus::COMPLETED->value : SppbStatus::RELEASE_IN_PROGRESS->value;
            if ($isAllCompleted) {
                $sppb->completed_at = now();
            }
            $sppb->save();

            return $release;
        });
    }
}
