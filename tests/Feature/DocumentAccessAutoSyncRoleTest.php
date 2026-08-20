<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DocumentAccesses\Pages\CreateDocumentAccess;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentAccessAutoSyncRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_access_creation_automatically_populates_role_relational_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->assignRole($superAdminRole);

        $batRole = Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web']);
        $plant = Plant::factory()->create();

        $this->actingAs($superAdmin);

        Livewire::test(CreateDocumentAccess::class)
            ->fillForm([
                'receiver_type' => 'role',
                'role_id' => $batRole->id,
                'access_items' => [
                    [
                        'plant_id' => $plant->id,
                        'department_id' => null,
                        'module' => 'sppb',
                        'can_view' => true,
                        'can_create' => true,
                        'can_edit' => true,
                        'can_delete' => false,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify that Spatie relational permissions table (role_has_permissions) has been automatically populated
        $batRole->refresh();
        $this->assertTrue($batRole->hasPermissionTo('view_any_sppbheader'));
        $this->assertTrue($batRole->hasPermissionTo('view_sppbheader'));
        $this->assertTrue($batRole->hasPermissionTo('create_sppbheader'));
        $this->assertTrue($batRole->hasPermissionTo('update_sppbheader'));
        $this->assertTrue($batRole->hasPermissionTo('verify_bat'));
    }

    public function test_document_access_creation_for_user_syncs_user_and_role_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->assignRole($superAdminRole);

        $batRole = Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web']);
        $userA = User::factory()->create();
        $userA->assignRole($batRole);

        $plant = Plant::factory()->create();

        $this->actingAs($superAdmin);

        Livewire::test(CreateDocumentAccess::class)
            ->fillForm([
                'receiver_type' => 'user',
                'user_id' => $userA->id,
                'access_items' => [
                    [
                        'plant_id' => $plant->id,
                        'department_id' => null,
                        'module' => 'goods_release',
                        'can_view' => true,
                        'can_create' => true,
                        'can_edit' => false,
                        'can_delete' => false,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $userA->refresh();
        $batRole->refresh();

        $this->assertTrue($userA->hasPermissionTo('view_any_goodsrelease'));
        $this->assertTrue($userA->hasPermissionTo('create_goodsrelease'));
        $this->assertTrue($batRole->hasPermissionTo('view_any_goodsrelease'));
    }
}
