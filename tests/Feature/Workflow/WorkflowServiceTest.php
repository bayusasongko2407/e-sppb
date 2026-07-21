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
use App\Exceptions\Workflow\StaleWorkflowCommandException;
use App\Exceptions\Workflow\UnauthorizedApprovalException;
use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowDelegation;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
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
            'approver_position_ids' => [Position::factory()->create()->id],
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
        $this->assertEquals(WorkflowCommandStatus::COMPLETED->value, $command->status);

        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_APPROVAL->value, $sppb->status);

        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $sppb->id,
            'to_status' => SppbStatus::SUBMISSION_QUEUED->value,
            'action' => 'SUBMIT_QUEUED',
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
        DB::table('user_positions')->insert([
            'user_id' => $nextApprover->id,
            'position_id' => $step2->approver_position_ids[0],
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

        $this->workflowService->queueApproval($data);
        $updatedStep = $firstStep->refresh();

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

        $this->workflowService->queueApproval($data);

        $sppb->refresh();
        $this->assertEquals(SppbStatus::REJECTED->value, $sppb->status);
        $this->assertEquals('Salah dokumen', $sppb->rejected_reason);
        $this->assertNull($sppb->current_approver_id);

        $instance->refresh();
        $this->assertEquals(WorkflowInstanceStatus::REJECTED->value, $instance->status);
    }

    public function test_manager_approval_with_skip_plant_manager_option(): void
    {
        [$sppb, $requester, $manager, $template, $step1, $step2] = $this->setupSppbAndWorkflow();

        // Pastikan step 1 adalah BAT
        $step1->update(['code' => 'BAT-01', 'name' => 'Verifikasi BAT']);

        // Kita butuh user untuk position step 2
        $nextApprover = User::factory()->create(['plant_id' => $sppb->plant_id]);
        DB::table('user_positions')->insert([
            'user_id' => $nextApprover->id,
            'position_id' => $step2->approver_position_ids[0],
            'is_active' => true,
        ]);

        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');

        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_VERIFICATION_BAT->value, $sppb->status);

        $secondStep = $instance->workflowInstanceSteps()->where('sequence', 2)->first();
        $secondStep->update(['code' => 'MAN-01', 'name' => 'Persetujuan Manager']);

        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        // Jalankan persetujuan langkah 1 (BAT)
        $data1 = new ApprovalDecisionData(
            workflowInstanceStepId: $firstStep->id,
            actorId: $manager->id,
            decision: 'APPROVE',
            commandUuid: 'cmd-uuid-1',
            correlationId: 'corr-1',
            remarks: 'BAT OK',
            delegatedFromId: null
        );
        $this->workflowService->queueApproval($data1);

        $sppb->refresh();
        $this->assertEquals(SppbStatus::WAITING_APPROVAL_MANAGER->value, $sppb->status);

        // Tambah step ke-3 (Plant Manager) di instance untuk mensimulasikan sisa step
        $thirdStep = WorkflowInstanceStep::create([
            'workflow_instance_id' => $instance->id,
            'sequence' => 3,
            'code' => 'PLNT-01',
            'name' => 'Persetujuan Plant Manager',
            'approver_type' => 'ROLE',
            'status' => 'QUEUED',
        ]);

        // Jalankan persetujuan Manager dengan requirePlantManager = false
        $data2 = new ApprovalDecisionData(
            workflowInstanceStepId: $secondStep->id,
            actorId: $nextApprover->id,
            decision: 'APPROVE',
            commandUuid: 'cmd-uuid-2',
            correlationId: 'corr-2',
            remarks: 'Manager OK',
            delegatedFromId: null,
            requirePlantManager: false
        );
        $this->workflowService->queueApproval($data2);

        $sppb->refresh();
        $instance->refresh();
        $thirdStep->refresh();

        $this->assertEquals(SppbStatus::APPROVED->value, $sppb->status);
        $this->assertEquals(WorkflowInstanceStatus::APPROVED->value, $instance->status);
        $this->assertEquals(WorkflowInstanceStepStatus::CANCELLED->value, $thirdStep->status);
    }

    public function test_multi_hop_and_circular_delegation_handling(): void
    {
        [$sppb, $requester, $manager, $template, $step1, $step2] = $this->setupSppbAndWorkflow();

        // User A (Manager), User B, User C
        $userB = User::factory()->create(['plant_id' => $sppb->plant_id]);
        $userC = User::factory()->create(['plant_id' => $sppb->plant_id]);

        DB::table('user_positions')->insert([
            'user_id' => $userC->id,
            'position_id' => $step2->approver_position_ids[0],
            'is_active' => true,
        ]);

        // Delegation chain: Manager -> UserB -> UserC
        WorkflowDelegation::create([
            'delegator_id' => $manager->id,
            'delegate_id' => $userB->id,
            'plant_id' => $sppb->plant_id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'reason' => 'Cuti',
            'is_active' => true,
        ]);

        WorkflowDelegation::create([
            'delegator_id' => $userB->id,
            'delegate_id' => $userC->id,
            'plant_id' => $sppb->plant_id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'reason' => 'Dinas Luar',
            'is_active' => true,
        ]);

        // Circular delegation: UserC -> Manager (A) to test loop protection
        WorkflowDelegation::create([
            'delegator_id' => $userC->id,
            'delegate_id' => $manager->id,
            'plant_id' => $sppb->plant_id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'reason' => 'Circular Delegation',
            'is_active' => true,
        ]);

        // Verify resolveDelegatorIds doesn't crash on circular loop and resolves multi-hop delegators
        $delegatorsForUserC = $this->workflowService->resolveDelegatorIds($userC->id);
        $this->assertContains($userB->id, $delegatorsForUserC);
        $this->assertContains($manager->id, $delegatorsForUserC);

        // User C can approve for Manager via multi-hop delegation
        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-123');
        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();

        $data = new ApprovalDecisionData(
            workflowInstanceStepId: $firstStep->id,
            actorId: $userC->id,
            decision: 'APPROVE',
            commandUuid: 'cmd-uuid-multihop',
            correlationId: 'corr-multihop',
            remarks: 'Multi-hop Delegation Approval OK',
            delegatedFromId: $manager->id
        );

        $this->workflowService->queueApproval($data);

        $firstStep->refresh();
        $this->assertEquals(WorkflowInstanceStepStatus::APPROVED->value, $firstStep->status);
    }

    public function test_requester_manager_fallback_when_manager_id_null_or_inactive(): void
    {
        [$sppb, $requester, $manager, $template, $step1, $step2] = $this->setupSppbAndWorkflow();

        // Requester manager is inactive
        $manager->update(['is_active' => false]);

        // Create a department manager as fallback
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $deptManager = User::factory()->create([
            'plant_id' => $sppb->plant_id,
            'department_id' => $sppb->department_id,
            'is_active' => true,
        ]);
        $deptManager->assignRole('manager');

        // User for step 2 position
        $step2User = User::factory()->create(['plant_id' => $sppb->plant_id]);
        DB::table('user_positions')->insert([
            'user_id' => $step2User->id,
            'position_id' => $step2->approver_position_ids[0],
            'is_active' => true,
        ]);

        $sppb->status = SppbStatus::SUBMISSION_QUEUED->value;
        $sppb->save();

        // Should resolve fallback deptManager without throwing ApproverNotFoundException
        $instance = $this->workflowService->generateWorkflow($sppb->id, 'corr-fallback');

        $firstStep = $instance->workflowInstanceSteps()->where('sequence', 1)->first();
        $this->assertDatabaseHas('workflow_step_approvers', [
            'workflow_instance_step_id' => $firstStep->id,
            'approver_id' => $deptManager->id,
            'status' => ApproverStatus::PENDING->value,
        ]);
    }
}
