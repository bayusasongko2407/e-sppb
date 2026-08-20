<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\GoodsReleases\Pages\ViewGoodsRelease;
use App\Models\GoodsRelease;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoodsReleaseNullSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_goods_release_with_null_sppb_headers_relation_renders_safely(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $sppb = SppbHeader::factory()->create();
        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'RELEASED',
        ]);

        $this->actingAs($user);

        Livewire::test(ViewGoodsRelease::class, ['record' => $release->getRouteKey()])
            ->assertSuccessful();
    }
}
