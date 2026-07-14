<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\DTOs\Workflow\ApprovalDecisionData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowInstanceStepStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\UserPosition;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = app(WorkflowService::class);
    }

    public function test_sppb_full_lifecycle(): void
    {
        // 1. Setup Master Data
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();
        
        $manager = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $department->id]);
        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $department->id, 'manager_id' => $manager->id]);
        
        $batPosition = Position::factory()->create(['name' => 'BAT Approver']);
        $batUser = User::factory()->create(['plant_id' => $plant->id]);
        UserPosition::factory()->create([
            'user_id' => $batUser->id,
            'position_id' => $batPosition->id,
            'is_active' => true,
        ]);

        $template = WorkflowTemplate::factory()->create([
            'document_type' => 'SPPB',
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'is_active' => true,
            'effective_from' => null,
            'effective_until' => null,
        ]);

        WorkflowStep::factory()->create([
            'workflow_template_id' => $template->id,
            'sequence' => 1,
            'approver_type' => 'REQUESTER_MANAGER',
            'approval_mode' => 'ANY',
            'minimum_approvals' => 1,
        ]);
        
        WorkflowStep::factory()->create([
            'workflow_template_id' => $template->id,
            'sequence' => 2,
            'approver_type' => 'POSITION',
            'approver_position_id' => $batPosition->id,
            'approval_mode' => 'ANY',
            'minimum_approvals' => 1,
        ]);

        // 2. Draft SPPB
        $sppb = SppbHeader::factory()->create([
            'status' => SppbStatus::DRAFT->value,
            'requester_id' => $requester->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        // 3. Submit SPPB
        $submitData = new SubmitSppbData(
            sppbHeaderId: $sppb->id,
            actorId: $requester->id,
            commandUuid: 'submit-uuid-123',
            correlationId: 'corr-123'
        );
        $this->workflowService->queueSubmission($submitData);
        $sppb->refresh();
        $this->assertEquals(SppbStatus::SUBMISSION_QUEUED->value, $sppb->status);

        // Job memproses submission:
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');
        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_APPROVAL->value, $sppb->status);
        $this->assertEquals(1, $sppb->current_step_sequence);
        $this->assertEquals($manager->id, $sppb->current_approver_id);

        $step1 = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        // 4. Manager Approval
        $approval1Data = new ApprovalDecisionData(
            workflowInstanceStepId: $step1->id,
            actorId: $manager->id,
            decision: 'APPROVE',
            commandUuid: 'approve-uuid-1',
            correlationId: 'corr-123',
            remarks: 'Disetujui manager',
            delegatedFromId: null
        );
        
        $this->workflowService->queueApproval($approval1Data);
        $this->workflowService->approve($approval1Data);
        
        $sppb->refresh();
        $this->assertEquals(2, $sppb->current_step_sequence);
        $this->assertEquals($batUser->id, $sppb->current_approver_id);
        
        $step2 = $instance->workflowInstanceSteps()->where('sequence', 2)->first();

        // 5. BAT Approval
        $approval2Data = new ApprovalDecisionData(
            workflowInstanceStepId: $step2->id,
            actorId: $batUser->id,
            decision: 'APPROVE',
            commandUuid: 'approve-uuid-2',
            correlationId: 'corr-123',
            remarks: 'Disetujui BAT',
            delegatedFromId: null
        );
        
        $this->workflowService->queueApproval($approval2Data);
        $this->workflowService->approve($approval2Data);
        
        $sppb->refresh();
        $this->assertEquals(SppbStatus::APPROVED->value, $sppb->status);
        $this->assertNull($sppb->current_approver_id);
        
        $instance->refresh();
        $this->assertEquals(WorkflowInstanceStatus::APPROVED->value, $instance->status);
        
        // Cek log status
        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $sppb->id,
            'to_status' => SppbStatus::APPROVED->value,
            'action' => 'SPPB_APPROVED'
        ]);
    }
}
