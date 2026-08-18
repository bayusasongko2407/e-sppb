<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GoodsRelease;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleGudangViewOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'gudang', 'guard_name' => 'web']);
    }

    public function test_user_with_role_gudang_can_view_sppb_and_goods_release_but_cannot_create_or_delete(): void
    {
        $gudangUser = User::factory()->create();
        $gudangUser->assignRole('gudang');

        $sppb = SppbHeader::factory()->create();
        $release = GoodsRelease::factory()->create(['sppb_header_id' => $sppb->id]);

        $this->assertTrue($gudangUser->can('viewAny', SppbHeader::class));
        $this->assertTrue($gudangUser->can('view', $sppb));
        $this->assertTrue($gudangUser->can('viewAny', GoodsRelease::class));
        $this->assertTrue($gudangUser->can('view', $release));

        // Gudang user cannot create or delete
        $this->assertFalse($gudangUser->can('create', GoodsRelease::class));
        $this->assertFalse($gudangUser->can('delete', $release));
    }
}
