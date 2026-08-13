<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\DTOs\GoodsRelease\GoodsReleaseItemData;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Item;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use App\Services\GoodsReleaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbDetailDeliveryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_delivery_status_updates_to_partially_delivered_and_delivered(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $unit = Unit::factory()->create();
        $item = Item::factory()->create(['unit_id' => $unit->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => SppbStatus::APPROVED->value,
        ]);

        $detail = SppbDetail::create([
            'sppb_header_id' => $sppb->id,
            'line_no' => 1,
            'item_id' => $item->id,
            'item_asset_name' => $item->name,
            'unit_id' => $unit->id,
            'quantity' => 100.00,
        ]);

        // 1. Initial state
        $this->assertEquals('PENDING', $detail->delivery_status);
        $this->assertEquals('Belum Dikirim', $detail->delivery_status_label);

        // 2. Partial shipment: Release 40 items
        $service = app(GoodsReleaseService::class);
        $release1 = $service->createGoodsRelease(new CreateGoodsReleaseData(
            sppbHeaderId: $sppb->id,
            actorId: $user->id,
            items: [
                new GoodsReleaseItemData(
                    sppbDetailId: $detail->id,
                    quantityReleased: 40.00,
                    conditionOnRelease: 'Baik'
                ),
            ],
            driverName: 'Pak Budi',
            vehicleNumber: 'B 1234 ABC',
            expeditionName: 'Internal'
        ));

        $detail->refresh();
        $this->assertEquals('PARTIALLY_RELEASED', $detail->delivery_status);
        $this->assertEquals('Pengiriman Sebagian', $detail->delivery_status_label);

        // Receive partial shipment
        $service->receiveGoodsRelease($release1, ['status' => 'DELIVERED']);
        $detail->refresh();
        $this->assertEquals('PARTIALLY_DELIVERED', $detail->delivery_status);
        $this->assertEquals('Diterima Sebagian', $detail->delivery_status_label);

        // 3. Final shipment: Release remaining 60 items
        $release2 = $service->createGoodsRelease(new CreateGoodsReleaseData(
            sppbHeaderId: $sppb->id,
            actorId: $user->id,
            items: [
                new GoodsReleaseItemData(
                    sppbDetailId: $detail->id,
                    quantityReleased: 60.00,
                    conditionOnRelease: 'Baik'
                ),
            ],
            driverName: 'Pak Budi',
            vehicleNumber: 'B 1234 ABC',
            expeditionName: 'Internal'
        ));

        $service->receiveGoodsRelease($release2, ['status' => 'DELIVERED']);

        $detail->refresh();
        $this->assertEquals('DELIVERED', $detail->delivery_status);
        $this->assertEquals('Diterima / Terkirim', $detail->delivery_status_label);
    }
}
