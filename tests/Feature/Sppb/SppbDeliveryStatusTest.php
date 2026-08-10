<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbDeliveryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_sppb_detail_delivery_status_without_goods_release(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => 'APPROVED',
        ]);

        $nonAssetDetail = SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
            'barcode_confirmed' => false,
            'item_asset_name' => 'Kabel UTP Cat6',
        ]);

        $this->assertEquals('NOT_SENT', $nonAssetDetail->delivery_status);
        $this->assertEquals('Belum Dikirim', $nonAssetDetail->delivery_status_label);
    }

    public function test_sppb_detail_delivery_status_when_goods_release_is_released(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => 'RELEASE_IN_PROGRESS',
        ]);

        $nonAssetDetail = SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
            'barcode_confirmed' => false,
            'item_asset_name' => 'Pompa Air Industri',
            'quantity' => 2,
        ]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'release_number' => 'SJ-20260729-0001',
            'status' => 'RELEASED',
            'delivery_date' => now()->toDateString(),
            'created_by_id' => $user->id,
        ]);

        GoodsReleaseItem::create([
            'goods_release_id' => $release->id,
            'sppb_detail_id' => $nonAssetDetail->id,
            'quantity_requested' => 2,
            'quantity_released' => 2,
        ]);

        $nonAssetDetail->refresh();

        $this->assertEquals('IN_TRANSIT', $nonAssetDetail->delivery_status);
        $this->assertEquals('Dalam Pengiriman', $nonAssetDetail->delivery_status_label);
    }

    public function test_sppb_detail_delivery_status_when_goods_release_is_delivered(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'status' => 'COMPLETED',
        ]);

        $nonAssetDetail = SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
            'barcode_confirmed' => false,
            'item_asset_name' => 'Baut M10',
            'quantity' => 100,
        ]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'release_number' => 'SJ-20260729-0002',
            'status' => 'DELIVERED',
            'created_by_id' => $user->id,
        ]);

        GoodsReleaseItem::create([
            'goods_release_id' => $release->id,
            'sppb_detail_id' => $nonAssetDetail->id,
            'quantity_requested' => 100,
            'quantity_released' => 100,
        ]);

        $nonAssetDetail->refresh();

        $this->assertEquals('DELIVERED', $nonAssetDetail->delivery_status);
        $this->assertEquals('Terkirim', $nonAssetDetail->delivery_status_label);
    }
}
