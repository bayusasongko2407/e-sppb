<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Workflow\ApprovalDecisionData;
use App\Enums\ApproverStatus;
use App\Http\Controllers\Controller;
use App\Models\WorkflowDelegation;
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

    /**
     * Daftar Delegasi Wewenang (Delegations).
     */
    public function listDelegations(Request $request)
    {
        $user = $request->user();

        $query = WorkflowDelegation::query()
            ->with(['delegator', 'delegate', 'plant'])
            ->orderBy('created_at', 'desc');

        if (! $user->hasRole('super_admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('delegator_id', $user->id)
                    ->orWhere('delegate_id', $user->id);
            });
        }

        $delegations = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar delegasi wewenang berhasil ditampilkan.',
            'data' => $delegations->items(),
            'meta' => [
                'current_page' => $delegations->currentPage(),
                'per_page' => $delegations->perPage(),
                'total' => $delegations->total(),
                'last_page' => $delegations->lastPage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Buat Delegasi Wewenang Baru.
     */
    public function createDelegation(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'delegate_id' => 'required|integer|exists:users,id|different:delegator_id',
            'plant_id' => 'nullable|integer|exists:plants,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'reason' => 'required|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $delegatorId = ($user->hasRole('super_admin') && $request->filled('delegator_id'))
            ? (int) $request->input('delegator_id')
            : (int) $user->id;

        $plantId = $request->input('plant_id') ?? $user->plant_id;

        $delegation = WorkflowDelegation::create([
            'delegator_id' => $delegatorId,
            'delegate_id' => (int) $request->input('delegate_id'),
            'plant_id' => $plantId,
            'starts_at' => $request->input('starts_at'),
            'ends_at' => $request->input('ends_at'),
            'reason' => $request->input('reason'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delegasi wewenang berhasil dibuat.',
            'data' => $delegation->load(['delegator', 'delegate', 'plant']),
            'timestamp' => now()->toIso8601String(),
        ], 201);
    }

    /**
     * Perbarui Delegasi Wewenang.
     */
    public function updateDelegation(Request $request, int|string $id)
    {
        $user = $request->user();

        $query = WorkflowDelegation::query();
        if (! $user->hasRole('super_admin')) {
            $query->where('delegator_id', $user->id);
        }

        $delegation = $query->where('id', $id)->firstOrFail();

        $request->validate([
            'delegate_id' => 'sometimes|integer|exists:users,id',
            'plant_id' => 'nullable|integer|exists:plants,id',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after_or_equal:starts_at',
            'reason' => 'sometimes|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        $delegation->update($request->only([
            'delegate_id',
            'plant_id',
            'starts_at',
            'ends_at',
            'reason',
            'is_active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Delegasi wewenang berhasil diperbarui.',
            'data' => $delegation->fresh(['delegator', 'delegate', 'plant']),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Batalkan / Nonaktifkan Delegasi Wewenang.
     */
    public function cancelDelegation(Request $request, int|string $id)
    {
        $user = $request->user();

        $query = WorkflowDelegation::query();
        if (! $user->hasRole('super_admin')) {
            $query->where('delegator_id', $user->id);
        }

        $delegation = $query->where('id', $id)->firstOrFail();
        $delegation->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Delegasi wewenang berhasil dinonaktifkan.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
