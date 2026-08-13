<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoodsReleaseResourceFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_create_goods_release_page_and_select_sppb(): void
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
            'status' => 'APPROVED',
        ]);

        SppbDetail::create([
            'sppb_header_id' => $sppb->id,
            'line_no' => 1,
            'item_id' => $item->id,
            'item_asset_name' => $item->name,
            'unit_id' => $unit->id,
            'quantity' => 10.00,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateGoodsRelease::class)
            ->set('data.sppbHeaders', [$sppb->id])
            ->assertHasNoFormErrors();
    }
}
