<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\DTOs\GoodsRelease\GoodsReleaseItemData;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use App\Services\GoodsReleaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateGoodsReleasePartialTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_select_release_in_progress_sppb_for_subsequent_goods_release(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $origin = Location::factory()->create(['plant_id' => $plant->id]);
        $dest = Location::factory()->create(['plant_id' => $plant->id]);
        $unit = Unit::factory()->create();
        $item = Item::factory()->create(['unit_id' => $unit->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $user->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $dest->id,
            'status' => SppbStatus::APPROVED->value,
        ]);

        $detail = SppbDetail::create([
            'sppb_header_id' => $sppb->id,
            'line_no' => 1,
            'item_id' => $item->id,
            'item_asset_name' => $item->name,
            'unit_id' => $unit->id,
            'quantity' => 10.00,
        ]);

        $this->actingAs($user);

        // 1. Create first Goods Release for 4 units
        $service = app(GoodsReleaseService::class);
        $service->createGoodsRelease(new CreateGoodsReleaseData(
            sppbHeaderId: $sppb->id,
            actorId: $user->id,
            items: [
                new GoodsReleaseItemData(
                    sppbDetailId: $detail->id,
                    quantityReleased: 4.00,
                    conditionOnRelease: 'Baik'
                ),
            ],
            driverName: 'Driver 1',
            vehicleNumber: 'B 1111 AAA',
            expeditionName: 'Internal'
        ));

        $sppb->refresh();
        $this->assertEquals(SppbStatus::RELEASE_IN_PROGRESS->value, $sppb->status);

        // 2. Create second Goods Release for remaining 6 units
        $release2 = $service->createGoodsRelease(new CreateGoodsReleaseData(
            sppbHeaderId: $sppb->id,
            actorId: $user->id,
            items: [
                new GoodsReleaseItemData(
                    sppbDetailId: $detail->id,
                    quantityReleased: 6.00,
                    conditionOnRelease: 'Baik'
                ),
            ],
            driverName: 'Driver 2',
            vehicleNumber: 'B 2222 BBB',
            expeditionName: 'Internal'
        ));

        $sppb->refresh();
        $this->assertEquals(SppbStatus::COMPLETED->value, $sppb->status);
        $this->assertNotNull($release2);
    }
}
