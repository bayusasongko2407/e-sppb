<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DocumentAccess;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentAccessGrantVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_granted_document_access_can_view_any_and_create_without_preexisting_spatie_permission(): void
    {
        $customRole = Role::firstOrCreate(['name' => 'Custom Staff', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($customRole);

        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        DocumentAccess::create([
            'user_id' => $user->id,
            'role_id' => null,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => false,
        ]);

        $this->actingAs($user);

        // Verify policy methods evaluate to true
        $this->assertTrue($user->can('viewAny', SppbHeader::class));
        $this->assertTrue($user->can('create', SppbHeader::class));

        $sppbHeader = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $this->assertTrue($user->can('view', $sppbHeader));
    }

    public function test_role_granted_document_access_grants_access_to_user_belonging_to_role(): void
    {
        $batRole = Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($batRole);

        $plant = Plant::factory()->create();

        DocumentAccess::create([
            'user_id' => null,
            'role_id' => $batRole->id,
            'plant_id' => $plant->id,
            'department_id' => null,
            'module' => 'sppb',
            'can_view' => true,
            'can_create' => false,
            'can_edit' => true,
            'can_delete' => false,
        ]);

        $this->actingAs($user);

        $this->assertTrue($user->can('viewAny', SppbHeader::class));

        $sppbHeader = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
        ]);

        $this->assertTrue($user->can('view', $sppbHeader));
    }
}
