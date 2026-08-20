<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoodsReleaseSaveFinalTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_final_creates_goods_release_with_items(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $sppb = SppbHeader::factory()->create();
        $detail = SppbDetail::factory()->create(['sppb_header_id' => $sppb->id, 'quantity' => 10]);

        $this->actingAs($user);

        Livewire::test(CreateGoodsRelease::class)
            ->set('data.sppbHeaders', [$sppb->id])
            ->set('data.driver_name', 'Budi')
            ->set('data.vehicle_number', 'L 1234 AB')
            ->set('data.expedition_name', 'Tetuko')
            ->set('data.delivery_date', date('Y-m-d'))
            ->set('data.goodsReleaseItems', [
                [
                    'sppb_detail_id' => $detail->id,
                    'quantity_requested' => 10,
                    'quantity_released' => 10,
                    'condition_on_release' => 'Bagus',
                ],
            ])
            ->set('desiredStatus', 'RELEASED')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('goods_releases', [
            'sppb_header_id' => $sppb->id,
            'status' => 'RELEASED',
        ]);

        $this->assertDatabaseHas('goods_release_items', [
            'sppb_detail_id' => $detail->id,
            'quantity_released' => 10,
        ]);
    }
}
