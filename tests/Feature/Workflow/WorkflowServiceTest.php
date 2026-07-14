<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\DTOs\Workflow\ApprovalDecisionData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Enums\WorkflowCommandStatus;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowInstanceStepStatus;
use App\Exceptions\Workflow\InvalidSppbTransitionException;
use App\Exceptions\Workflow\StaleWorkflowCommandException;
use App\Exceptions\Workflow\UnauthorizedApprovalException;
use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowDelegation;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = app(WorkflowService::class);
    }

    private function setupSppbAndWorkflow(): array
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();
        $manager = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $department->id]);
        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $department->id, 'manager_id' => $manager->id]);
        
        $template = WorkflowTemplate::factory()->create([
            'document_type' => 'SPPB',
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'is_active' => true,
            'effective_from' => null,
            'effective_until' => null,
        ]);

        $step1 = WorkflowStep::factory()->create([
            'workflow_template_id' => $template->id,
            'sequence' => 1,
            'approver_type' => 'REQUESTER_MANAGER',
            'approval_mode' => 'ANY',
            'minimum_approvals' => 1,
        ]);
        
        $step2 = WorkflowStep::factory()->create([
            'workflow_template_id' => $template->id,
            'sequence' => 2,
            'approver_type' => 'POSITION',
            'approver_position_id' => Position::factory()->create()->id,
            'approval_mode' => 'ANY',
            'minimum_approvals' => 1,
        ]);

        $sppb = SppbHeader::factory()->create([
            'status' => SppbStatus::DRAFT->value,
            'requester_id' => $requester->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        return [$sppb, $requester, $manager, $template, $step1, $step2];
    }

    public function test_queue_submission_creates_command_and_updates_status(): void
    {
        [$sppb, $requester] = $this->setupSppbAndWorkflow();

        $data = new SubmitSppbData(
            sppbHeaderId: $sppb->id,
            actorId: $requester->id,
            commandUuid: 'uuid-test-123',
            correlationId: 'corr-123'
        );

        $command = $this->workflowService->queueSubmission($data);

        $this->assertEquals('uuid-test-123', $command->command_uuid);
        $this->assertEquals(WorkflowCommandStatus::QUEUED->value, $command->status);
        
        $sppb->refresh();
        $this->assertEquals(SppbStatus::SUBMISSION_QUEUED->value, $sppb->status);
        
        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $sppb->id,
            'to_status' => SppbStatus::SUBMISSION_QUEUED->value,
            'action' => 'SUBMIT_QUEUED'
        ]);
    }

    public function test_queue_submission_prevents_duplicate_commands(): void
    {
        [$sppb, $requester] = $this->setupSppbAndWorkflow();

        $data = new SubmitSppbData(
            sppbHeaderId: $sppb->id,
            actorId: $requester->id,
            commandUuid: 'uuid-test-123',
            correlationId: 'corr-123'
        );

        $this->workflowService->queueSubmission($data);

        $this->expectException(StaleWorkflowCommandException::class);
        
        // Coba submit ulang dengan state yang dibalikin ke DRAFT supaya lolos validasi status
        SppbHeader::where('id', $sppb->id)->update(['status' => SppbStatus::DRAFT->value]);
        
        $this->workflowService->queueSubmission($data);
    }

    public function test_generate_workflow_creates_instance_and_assigns_approvers(): void
    {
        [$sppb, $requester, $manager, $template, $step1, $step2] = $this->setupSppbAndWorkflow();

        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();

        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');

        $this->assertEquals(WorkflowInstanceStatus::IN_PROGRESS->value, $instance->status);
        
        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_APPROVAL->value, $sppb->status);
        $this->assertEquals($instance->id, $sppb->current_workflow_instance_id);
        $this->assertEquals(1, $sppb->current_step_sequence);
        $this->assertEquals($manager->id, $sppb->current_approver_id); // Manager assigned

        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();
        $this->assertEquals(WorkflowInstanceStepStatus::PENDING->value, $firstStep->status);

        $this->assertDatabaseHas('workflow_step_approvers', [
            'workflow_instance_step_id' => $firstStep->id,
            'approver_id' => $manager->id,
            'status' => ApproverStatus::PENDING->value,
        ]);
    }

    public function test_queue_approval_validates_authorization(): void
    {
        [$sppb, $requester, $manager] = $this->setupSppbAndWorkflow();
        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');
        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        // Actor lain yang bukan manager
        $unauthorizedActor = User::factory()->create();

        $data = new ApprovalDecisionData(
            workflowInstanceStepId: $firstStep->id,
            actorId: $unauthorizedActor->id,
            decision: 'APPROVE',
            commandUuid: 'cmd-uuid-1',
            correlationId: 'corr-1',
            remarks: 'OK',
            delegatedFromId: null
        );

        $this->expectException(UnauthorizedApprovalException::class);
        $this->workflowService->queueApproval($data);
    }

    public function test_approve_advances_workflow_to_next_step(): void
    {
        [$sppb, $requester, $manager, $template, $step1, $step2] = $this->setupSppbAndWorkflow();
        
        // Kita butuh user untuk position step 2
        $nextApprover = User::factory()->create(['plant_id' => $sppb->plant_id]);
        \Illuminate\Support\Facades\DB::table('user_positions')->insert([
            'user_id' => $nextApprover->id,
            'position_id' => $step2->approver_position_id,
            'is_active' => true,
        ]);

        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');
        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        $data = new ApprovalDecisionData(
            workflowInstanceStepId: $firstStep->id,
            actorId: $manager->id,
            decision: 'APPROVE',
            commandUuid: 'cmd-uuid-1',
            correlationId: 'corr-1',
            remarks: 'OK',
            delegatedFromId: null
        );

        $updatedStep = $this->workflowService->approve($data);

        $this->assertEquals(WorkflowInstanceStepStatus::APPROVED->value, $updatedStep->status);

        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_APPROVAL->value, $sppb->status);
        $this->assertEquals(2, $sppb->current_step_sequence);
        $this->assertEquals($nextApprover->id, $sppb->current_approver_id);

        $nextInstanceStep = $instance->workflowInstanceSteps()->where('sequence', 2)->first();
        $this->assertEquals(WorkflowInstanceStepStatus::PENDING->value, $nextInstanceStep->status);
    }

    public function test_reject_cancels_workflow(): void
    {
        [$sppb, $requester, $manager] = $this->setupSppbAndWorkflow();
        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');
        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        $data = new ApprovalDecisionData(
            workflowInstanceStepId: $firstStep->id,
            actorId: $manager->id,
            decision: 'REJECT',
            commandUuid: 'cmd-uuid-1',
            correlationId: 'corr-1',
            remarks: 'Salah dokumen',
            delegatedFromId: null
        );

        $this->workflowService->reject($data);

        $sppb->refresh();
        $this->assertEquals(SppbStatus::REJECTED->value, $sppb->status);
        $this->assertEquals('Salah dokumen', $sppb->rejected_reason);
        $this->assertNull($sppb->current_approver_id);

        $instance->refresh();
        $this->assertEquals(WorkflowInstanceStatus::REJECTED->value, $instance->status);
    }
}
