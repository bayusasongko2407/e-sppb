<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\ApprovalDecisionData;
use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SppbNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_receives_notification_when_sppb_is_rejected(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $loc1 = Location::factory()->create(['plant_id' => $plant->id]);
        $loc2 = Location::factory()->create(['plant_id' => $plant->id]);

        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        $approver = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);

        $template = WorkflowTemplate::factory()->create([
            'document_type' => 'SPPB',
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $header = SppbHeader::create([
            'document_number' => 'ENG-SPPB/2026/07/0099',
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'origin_location_id' => $loc1->id,
            'destination_location_id' => $loc2->id,
            'needed_name' => 'Tes Rejection Notification',
            'request_date' => now(),
            'status' => SppbStatus::WAITING_APPROVAL->value,
        ]);

        $instance = WorkflowInstance::create([
            'uuid' => (string) Str::uuid(),
            'workflow_template_id' => $template->id,
            'template_version' => 1,
            'sppb_header_id' => $header->id,
            'status' => 'IN_PROGRESS',
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $instance->id,
            'sequence' => 1,
            'code' => 'MGR',
            'name' => 'Manager Approval',
            'approver_type' => 'USER',
            'approval_mode' => 'ANY',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $approver->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        $header->update([
            'current_workflow_instance_id' => $instance->id,
            'current_step_sequence' => 1,
        ]);

        $workflowService = app(WorkflowServiceContract::class);
        $workflowService->queueApproval(new ApprovalDecisionData(
            workflowInstanceStepId: $step->id,
            actorId: $approver->id,
            commandUuid: (string) Str::uuid(),
            decision: 'reject',
            remarks: 'Alasan penolakan pengujian',
        ));

        // Verify database notification is created for requester
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $requester->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertEquals('SPPB Ditolak', $notification->data['title']);
        $this->assertStringContainsString('ENG-SPPB/2026/07/0099', $notification->data['body']);
        $this->assertStringContainsString('Alasan penolakan pengujian', $notification->data['body']);
    }
}
