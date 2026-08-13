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
     * Daftar Tugas Persetujuan (Approval Tasks).
     *
     * Menampilkan daftar tugas persetujuan pending yang ditugaskan kepada pengguna yang sedang login (termasuk tugas hasil delegasi wewenang).
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
     * Detail Alur Kerja (Workflow Instance).
     *
     * Menampilkan detail tahapan alur kerja persetujuan berdasarkan UUID instansi.
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
     * Setujui Step Persetujuan (Approve Step).
     *
     * Memproses persetujuan dokumen SPPB pada tahapan tertentu.
     * Mengembalikan response JSON 200 OK jika berhasil, atau 403 JSON jika pengguna tidak berwenang.
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
     * Tolak Dokumen SPPB (Reject Step).
     *
     * Memproses penolakan dokumen SPPB pada tahapan persetujuan aktif.
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
     * Minta Revisi Dokumen SPPB (Request Revision Step).
     *
     * Mengembalikan dokumen SPPB ke pemohon untuk direvisi pada tahapan persetujuan aktif.
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
