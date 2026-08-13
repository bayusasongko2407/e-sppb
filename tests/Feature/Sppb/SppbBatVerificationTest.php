<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

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
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SppbBatVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_show_automatically_updates_status_to_process_verification_bat_when_opened_by_bat(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $batUser = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);
        $batUser->givePermissionTo('view_sppbheader');

        $workflowInstance = WorkflowInstance::factory()->create([
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'status' => SppbStatus::WAITING_VERIFICATION_BAT->value,
            'current_workflow_instance_id' => $workflowInstance->id,
            'current_step_sequence' => 1,
        ]);

        $workflowInstance->update(['sppb_header_id' => $sppb->id]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_BAT_VERIFICATION',
            'name' => 'Verifikasi Tim BAT',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $batUser->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        Sanctum::actingAs($batUser);

        $response = $this->getJson('/api/v1/sppb/'.$sppb->uuid);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', SppbStatus::PROCESS_VERIFICATION_BAT->value);

        $this->assertDatabaseHas('sppb_headers', [
            'id' => $sppb->id,
            'status' => SppbStatus::PROCESS_VERIFICATION_BAT->value,
        ]);

        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $sppb->id,
            'actor_id' => $batUser->id,
            'action' => 'BAT_OPENED',
            'from_status' => SppbStatus::WAITING_VERIFICATION_BAT->value,
            'to_status' => SppbStatus::PROCESS_VERIFICATION_BAT->value,
        ]);
    }
}
