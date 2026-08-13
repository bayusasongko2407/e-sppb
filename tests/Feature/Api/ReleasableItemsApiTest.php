<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

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
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReleasableItemsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_calculates_partial_shipment_remaining_quantities_correctly(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);
        $user->givePermissionTo('view_sppbheader');

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

        Sanctum::actingAs($user);

        // 1. Initial state: 100 remaining, PENDING
        $response1 = $this->getJson('/api/v1/sppb/'.$sppb->uuid.'/releasable-items');
        $response1->assertStatus(200);
        $response1->assertJsonPath('data.items.0.quantity_requested', 100);
        $response1->assertJsonPath('data.items.0.quantity_already_released', 0);
        $response1->assertJsonPath('data.items.0.quantity_remaining', 100);
        $response1->assertJsonPath('data.items.0.delivery_status', 'PENDING');

        // 2. Partial shipment: Release 40 items
        $service = app(GoodsReleaseService::class);
        $service->createGoodsRelease(new CreateGoodsReleaseData(
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

        // 3. Second state: 60 remaining, PARTIALLY_DELIVERED
        $response2 = $this->getJson('/api/v1/sppb/'.$sppb->uuid.'/releasable-items');
        $response2->assertStatus(200);
        $response2->assertJsonPath('data.items.0.quantity_already_released', 40);
        $response2->assertJsonPath('data.items.0.quantity_remaining', 60);
        $response2->assertJsonPath('data.items.0.delivery_status', 'PARTIALLY_DELIVERED');
        $response2->assertJsonCount(1, 'data.releasable_items');

        // 4. Final shipment: Release remaining 60 items
        $service->createGoodsRelease(new CreateGoodsReleaseData(
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

        // 5. Fully shipped: 0 remaining, DELIVERED, empty releasable_items
        $response3 = $this->getJson('/api/v1/sppb/'.$sppb->uuid.'/releasable-items');
        $response3->assertStatus(200);
        $response3->assertJsonPath('data.items.0.quantity_already_released', 100);
        $response3->assertJsonPath('data.items.0.quantity_remaining', 0);
        $response3->assertJsonPath('data.items.0.delivery_status', 'DELIVERED');
        $response3->assertJsonCount(0, 'data.releasable_items');
    }
}
