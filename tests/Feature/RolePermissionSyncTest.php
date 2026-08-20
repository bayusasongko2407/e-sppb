<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_resource_dynamically_captures_new_custom_permissions(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Custom new permission that is not in hardcoded modules
        $newPermission = Permission::firstOrCreate(['name' => 'custom_new_feature', 'guard_name' => 'web']);

        $this->actingAs($superAdmin);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Test Custom Role',
                'permissions_other' => [$newPermission->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdRole = Role::where('name', 'Test Custom Role')->first();
        $this->assertNotNull($createdRole);
        $this->assertTrue($createdRole->hasPermissionTo('custom_new_feature'));
    }
}
