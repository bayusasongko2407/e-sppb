<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleFeatureTest extends TestCase
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

    public function test_super_admin_can_access_role_management(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $permission = Permission::firstOrCreate(['name' => 'view_any_role', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo($permission);

        // Clear Spatie cache after database changes
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $response = $this->actingAs($superAdmin)
            ->get(ListRoles::getUrl());

        $response->assertStatus(200);
    }

    public function test_can_create_custom_role_with_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $permission = Permission::firstOrCreate(['name' => 'view_any_role', 'guard_name' => 'web']);
        $permissionCreate = Permission::firstOrCreate(['name' => 'create_role', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo($permission, $permissionCreate);

        $permission1 = Permission::firstOrCreate(['name' => 'create_plant', 'guard_name' => 'web']);
        $permission2 = Permission::firstOrCreate(['name' => 'view_plant', 'guard_name' => 'web']);

        $this->actingAs($superAdmin);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'is_superadmin' => false,
                'name' => 'test_custom_role',
                'permissions_plant' => [$permission1->id, $permission2->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::findByName('test_custom_role', 'web');
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('create_plant'));
        $this->assertTrue($role->hasPermissionTo('view_plant'));
    }

    public function test_can_create_super_admin_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $permission = Permission::firstOrCreate(['name' => 'view_any_role', 'guard_name' => 'web']);
        $permissionCreate = Permission::firstOrCreate(['name' => 'create_role', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo($permission, $permissionCreate);

        $this->actingAs($superAdmin);

        // Delete seeded super_admin so we can test creating it
        Role::where('name', 'super_admin')->delete();

        Livewire::test(CreateRole::class)
            ->fillForm([
                'is_superadmin' => true,
                'name' => 'super_admin',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::findByName('super_admin', 'web');
        $this->assertNotNull($role);
    }
}
