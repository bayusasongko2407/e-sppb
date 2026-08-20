<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RecycleBins\GoodsReleaseRecycleBinResource;
use App\Filament\Resources\RecycleBins\Pages\ListGoodsReleaseRecycleBins;
use App\Filament\Resources\RecycleBins\Pages\ListRecycleBins;
use App\Filament\Resources\RecycleBins\RecycleBinResource;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecycleBinSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_has_comprehensive_access_to_sppb_and_goods_release_recycle_bins(): void
    {
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->assignRole($superAdminRole);

        $plant = Plant::factory()->create();

        // Create trashed SPPB
        $trashedSppb = SppbHeader::factory()->create(['plant_id' => $plant->id]);
        $trashedSppb->delete();

        // Create trashed GoodsRelease
        $trashedRelease = GoodsRelease::factory()->create([
            'sppb_header_id' => $trashedSppb->id,
            'release_number' => 'SJ-TRASHED-001',
        ]);
        $trashedRelease->delete();

        $this->actingAs($superAdmin);

        $this->assertTrue(RecycleBinResource::canViewAny());
        $this->assertTrue(GoodsReleaseRecycleBinResource::canViewAny());

        Livewire::test(ListRecycleBins::class)
            ->assertSeeText($trashedSppb->document_number);

        Livewire::test(ListGoodsReleaseRecycleBins::class)
            ->assertSeeText('SJ-TRASHED-001');
    }

    public function test_non_superadmin_cannot_access_sppb_or_goods_release_recycle_bins(): void
    {
        $regularRole = Role::firstOrCreate(['name' => 'Pemohon', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($regularRole);

        $this->actingAs($user);

        $this->assertFalse(RecycleBinResource::canViewAny());
        $this->assertFalse(GoodsReleaseRecycleBinResource::canViewAny());
    }
}
