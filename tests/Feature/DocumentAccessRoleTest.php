<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SppbHeaders\Pages\ListSppbHeaders;
use App\Models\Department;
use App\Models\DocumentAccess;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentAccessRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_role_level_document_access_can_view_sppb_headers(): void
    {
        $viewPermission = Permission::firstOrCreate(['name' => 'view_any_sppbheader', 'guard_name' => 'web']);
        $batRole = Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web']);
        $batRole->givePermissionTo($viewPermission);

        $user = User::factory()->create([
            'plant_id' => null,
            'department_id' => null,
        ]);
        $user->assignRole($batRole);

        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $sppbHeader = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'status' => 'WAITING_VERIFICATION_BAT',
        ]);

        DocumentAccess::create([
            'user_id' => null,
            'role_id' => $batRole->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
            'can_view' => true,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ListSppbHeaders::class)
            ->assertCanSeeTableRecords([$sppbHeader]);
    }

    public function test_user_with_wildcard_plant_department_document_access_can_view_sppb_headers(): void
    {
        $viewPermission = Permission::firstOrCreate(['name' => 'view_any_sppbheader', 'guard_name' => 'web']);
        $batRole = Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web']);
        $batRole->givePermissionTo($viewPermission);

        $user = User::factory()->create([
            'plant_id' => null,
            'department_id' => null,
        ]);
        $user->assignRole($batRole);

        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $sppbHeader = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'status' => 'WAITING_VERIFICATION_BAT',
        ]);

        DocumentAccess::create([
            'user_id' => null,
            'role_id' => $batRole->id,
            'plant_id' => null,
            'department_id' => null,
            'module' => 'sppb',
            'can_view' => true,
            'can_create' => false,
            'can_edit' => false,
            'can_delete' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ListSppbHeaders::class)
            ->assertCanSeeTableRecords([$sppbHeader]);
    }
}
