<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowApprovalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_approver_can_get_approval_tasks_without_403_forbidden(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $approver = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        Role::firstOrCreate(['name' => 'approver']);
        $approver->assignRole('approver');

        $workflowInstance = WorkflowInstance::factory()->create([
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_MGR_APPROVAL',
            'name' => 'Persetujuan Manager',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $approver->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        Sanctum::actingAs($approver);

        $response = $this->getJson('/api/v1/workflow/tasks');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
    }

    public function test_approver_can_approve_step_and_return_json_200(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $approver = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        Role::firstOrCreate(['name' => 'manager']);
        $approver->assignRole('manager');

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'status' => SppbStatus::WAITING_APPROVAL->value,
        ]);

        $workflowInstance = WorkflowInstance::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_MGR_APPROVAL',
            'name' => 'Persetujuan Manager',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $approver->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        Sanctum::actingAs($approver);

        $response = $this->postJson('/api/v1/workflow/steps/'.$step->id.'/approve', [
            'remarks' => 'Disetujui via API',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Persetujuan SPPB berhasil diproses.');
    }

    public function test_unauthorized_user_receives_json_403_response_and_not_html(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $otherUser = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $workflowInstance = WorkflowInstance::factory()->create([
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_MGR_APPROVAL',
            'name' => 'Persetujuan Manager',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/v1/workflow/steps/'.$step->id.'/approve', [
            'remarks' => 'Coba approve user lain',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['success', 'message', 'data', 'errors', 'timestamp']);
    }
}
