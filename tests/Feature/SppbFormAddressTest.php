<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SppbHeaders\Pages\EditSppbHeader;
use App\Models\Department;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SppbFormAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_address_fields_are_populated_when_editing_sppb_header(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $originLocation = Location::factory()->create([
            'name' => 'Gudang Asal',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
        ]);

        $destLocation = Location::factory()->create([
            'name' => 'Gudang Tujuan',
            'address' => 'Jl. Industri No. 456, Surabaya',
        ]);

        $sppbHeader = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'requester_id' => $user->id,
            'origin_location_id' => $originLocation->id,
            'destination_location_id' => $destLocation->id,
            'status' => 'DRAFT',
        ]);

        $this->actingAs($user);

        Livewire::test(EditSppbHeader::class, [
            'record' => $sppbHeader->getRouteKey(),
        ])
            ->assertFormSet([
                'origin_address_display' => 'Jl. Merdeka No. 123, Jakarta',
                'destination_address_display' => 'Jl. Industri No. 456, Surabaya',
            ]);
    }
}
