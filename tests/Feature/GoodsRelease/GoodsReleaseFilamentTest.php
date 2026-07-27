<?php

declare(strict_types=1);

namespace Tests\Feature\GoodsRelease;

use App\Filament\Resources\GoodsReleases\Pages\ListGoodsReleases;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GoodsReleaseFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Force local env to allow Filament panel access under test environment
        config(['app.env' => 'local']);

        // Sync permissions
        $this->artisan('auth:sync-permissions');

        // Clear Spatie cached permissions
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed basic roles
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_goods_releases_index_page_loads_successfully_for_super_admin(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        // Clear Spatie cache after database changes
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $response = $this->actingAs($user)->get(ListGoodsReleases::getUrl());

        // Verify that the page loads without 500 errors (like class not found)
        $response->assertStatus(200);
    }
}
