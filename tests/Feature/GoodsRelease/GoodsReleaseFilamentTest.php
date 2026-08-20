<?php

declare(strict_types=1);

namespace Tests\Feature\GoodsRelease;

use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\ListGoodsReleases;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_can_create_pure_manual_goods_release(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        Unit::factory()->create(['name' => 'Unit', 'is_active' => true]);

        $this->actingAs($user);

        Livewire::test(CreateGoodsRelease::class)
            ->fillForm([
                'is_manual' => true,
                'manual_release_number' => 'SJ-MAN-999',
                'driver_name' => 'Budi Driver',
                'vehicle_number' => 'L 1234 ABC',
                'expedition_name' => 'JNE',
                'sender_name' => 'Gudang Sidoarjo',
                'sender_address' => 'Jl. Industri Sidoarjo',
                'receiver_name' => 'Vendor Service Jaya',
                'receiver_address' => 'Jl. Bengkel Surabaya',
                'goodsReleaseItems' => [
                    [
                        'item_name' => 'Pompa Air Submersible',
                        'item_type' => 'Non Asset',
                        'barcode_code' => 'PMP-888',
                        'quantity_released' => 2,
                        'unit_name' => 'Unit',
                        'condition_on_release' => 'Untuk Service',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('goods_releases', [
            'is_manual' => true,
            'manual_release_number' => 'SJ-MAN-999',
            'sender_name' => 'Gudang Sidoarjo',
        ]);

        $this->assertDatabaseHas('goods_release_items', [
            'item_name' => 'Pompa Air Submersible',
            'quantity_released' => 2,
            'unit_name' => 'Unit',
        ]);
    }
}
