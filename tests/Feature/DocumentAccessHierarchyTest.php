<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DocumentAccesses\Pages\CreateDocumentAccess;
use App\Models\DocumentAccess;
use App\Models\Plant;
use App\Models\User;
use App\Services\DocumentAccessSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentAccessHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_automatically_grants_view_any_and_view_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->assignRole($superAdminRole);

        $testRole = Role::firstOrCreate(['name' => 'Test Access Role', 'guard_name' => 'web']);
        $plant = Plant::factory()->create();

        $this->actingAs($superAdmin);

        Livewire::test(CreateDocumentAccess::class)
            ->fillForm([
                'receiver_type' => 'role',
                'role_id' => $testRole->id,
                'access_items' => [
                    [
                        'plant_id' => $plant->id,
                        'department_id' => null,
                        'module' => 'sppb',
                        'can_view' => false,
                        'can_create' => true,
                        'can_edit' => false,
                        'can_delete' => false,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $testRole->refresh();

        $this->assertTrue($testRole->hasPermissionTo('view_any_sppbheader'));
        $this->assertTrue($testRole->hasPermissionTo('view_sppbheader'));
        $this->assertTrue($testRole->hasPermissionTo('create_sppbheader'));
    }

    public function test_user_has_document_access_returns_true_for_view_action_when_can_edit_is_true(): void
    {
        $user = User::factory()->create();
        $spatieRole = Role::firstOrCreate(['name' => 'Pemohon', 'guard_name' => 'web']);
        $user->assignRole($spatieRole);

        $accessItems = [
            [
                'module' => 'sppb',
                'can_view' => false,
                'can_create' => false,
                'can_edit' => true,
                'can_delete' => false,
            ],
        ];

        DocumentAccess::create([
            'user_id' => $user->id,
            'role_id' => null,
            'plant_id' => null,
            'department_id' => null,
            'module' => 'sppb',
            'can_view' => false,
            'can_create' => false,
            'can_edit' => true,
            'can_delete' => false,
        ]);

        DocumentAccessSyncService::syncAccessPermissions($user->id, null, $accessItems);

        $this->assertTrue($user->hasDocumentAccess('sppb', 'view'));
    }
}
