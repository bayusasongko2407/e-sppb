<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Workflow\ApprovalDecisionData;
use App\Enums\ApproverStatus;
use App\Http\Controllers\Controller;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStepApprover;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkflowTaskController extends Controller
{
    public function __construct(
        private readonly WorkflowService $workflowService
    ) {}

    /**
     * Display a listing of pending approval tasks for current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get pending step approvals assigned to the current user
        $tasks = WorkflowStepApprover::where('approver_id', $user->id)
            ->where('status', ApproverStatus::PENDING->value)
            ->with([
                'workflowInstanceStep.workflowInstance.sppbHeader.plant',
                'workflowInstanceStep.workflowInstance.sppbHeader.department',
                'workflowInstanceStep.workflowInstance.sppbHeader.requester',
                'workflowInstanceStep.workflowInstance.sppbHeader.originLocation',
                'workflowInstanceStep.workflowInstance.sppbHeader.destinationLocation',
                'workflowInstanceStep.workflowInstance.sppbHeader.details.item',
            ])
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas persetujuan berhasil ditampilkan.',
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Show detail of a workflow instance.
     */
    public function showInstance(Request $request, string $uuid)
    {
        $instance = WorkflowInstance::where('uuid', $uuid)
            ->with([
                'workflowInstanceSteps.stepApprovers.approver',
                'sppbHeader.plant',
                'sppbHeader.department',
                'sppbHeader.requester',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail alur kerja berhasil ditampilkan.',
            'data' => $instance,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Approve a workflow step.
     */
    public function approve(Request $request, int $stepId)
    {
        $dto = new ApprovalDecisionData(
            workflowInstanceStepId: (int) $stepId,
            actorId: (int) $request->user()->id,
            commandUuid: (string) Str::uuid(),
            decision: 'approve',
            remarks: $request->input('remarks', $request->input('note')),
            correlationId: (string) Str::uuid()
        );

        $this->workflowService->queueApproval($dto);

        return response()->json([
            'success' => true,
            'message' => 'Persetujuan SPPB berhasil diproses.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Reject a workflow step.
     */
    public function reject(Request $request, int $stepId)
    {
        $request->validate([
            'remarks' => 'required|string|min:5|max:1000',
        ]);

        $dto = new ApprovalDecisionData(
            workflowInstanceStepId: (int) $stepId,
            actorId: (int) $request->user()->id,
            commandUuid: (string) Str::uuid(),
            decision: 'reject',
            remarks: $request->input('remarks'),
            correlationId: (string) Str::uuid()
        );

        $this->workflowService->queueApproval($dto);

        return response()->json([
            'success' => true,
            'message' => 'Penolakan SPPB berhasil diproses.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Request revision for a workflow step.
     */
    public function requestRevision(Request $request, int $stepId)
    {
        $request->validate([
            'remarks' => 'required|string|min:5|max:1000',
        ]);

        $dto = new ApprovalDecisionData(
            workflowInstanceStepId: (int) $stepId,
            actorId: (int) $request->user()->id,
            commandUuid: (string) Str::uuid(),
            decision: 'revision',
            remarks: $request->input('remarks'),
            correlationId: (string) Str::uuid()
        );

        $this->workflowService->queueApproval($dto);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan revisi SPPB berhasil diproses.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
