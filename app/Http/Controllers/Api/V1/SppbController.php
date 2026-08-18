<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Sppb\CreateSppbData;
use App\DTOs\Sppb\SppbDetailData;
use App\DTOs\Workflow\ApprovalDecisionData;
use App\Enums\ApproverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Sppb\CreateSppbRequest;
use App\Http\Requests\Api\V1\Sppb\SubmitSppbRequest;
use App\Http\Requests\Api\V1\Sppb\UpdateSppbRequest;
use App\Http\Resources\Api\V1\SppbResource;
use App\Models\Department;
use App\Models\GoodsReleaseItem;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\SppbStatusLog;
use App\Models\Unit;
use App\Models\WorkflowStepApprover;
use App\Services\SppbService;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SppbController extends Controller
{
    public function __construct(
        private readonly SppbService $sppbService,
        private readonly WorkflowService $workflowService
    ) {}

    public function index(Request $request)
    {
        $query = SppbHeader::query()
            ->with([
                'plant',
                'department',
                'requester',
                'originLocation',
                'destinationLocation',
                'details.item',
                'details.unit',
                'sppbStatusLogs.actor.positions.position',
                'sppbStatusLogs.actor.roles',
                'sppbStatusLogs.workflowInstanceStep',
            ])
            ->orderBy($request->query('sort', 'created_at'), $request->query('direction', 'desc'));

        if ($request->has('status')) {
            $status = $request->query('status');
            $normalizedStatus = match (strtoupper((string) $status)) {
                'SUBMITTED' => 'WAITING_APPROVAL',
                'RELEASED' => 'RELEASE_IN_PROGRESS',
                default => $status,
            };
            $query->where('status', $normalizedStatus);
        }
        if ($request->has('plant_id')) {
            $query->where('plant_id', $request->query('plant_id'));
        }
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', '%'.$search.'%')
                    ->orWhere('needed_name', 'like', '%'.$search.'%')
                    ->orWhere('purpose', 'like', '%'.$search.'%')
                    ->orWhere('remarks', 'like', '%'.$search.'%')
                    ->orWhereHas('requester', function ($rq) use ($search) {
                        $rq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('nik', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $perPage = (int) ($request->query('per_page') ?? $request->query('limit') ?? 15);
        $sppbs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar SPPB berhasil ditampilkan.',
            'data' => SppbResource::collection($sppbs),
            'meta' => [
                'current_page' => $sppbs->currentPage(),
                'per_page' => $sppbs->perPage(),
                'total' => $sppbs->total(),
                'last_page' => $sppbs->lastPage(),
            ],
            'links' => [
                'first' => $sppbs->url(1),
                'last' => $sppbs->url($sppbs->lastPage()),
                'prev' => $sppbs->previousPageUrl(),
                'next' => $sppbs->nextPageUrl(),
            ],
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    public function store(CreateSppbRequest $request)
    {
        $dto = new CreateSppbData(
            plantId: (int) $request->plant_id,
            departmentId: (int) $request->department_id,
            originLocationId: (int) $request->origin_location_id,
            destinationLocationId: (int) $request->destination_location_id,
            neededName: $request->needed_name,
            requestDate: $request->request_date,
            dateNeeded: $request->date_needed,
            purpose: $request->purpose,
            isUrgent: $request->boolean('is_urgent', false),
            requesterId: $request->user()->id,
        );

        $sppb = $this->sppbService->createDraft($dto);

        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $unitId = Unit::where('name', 'like', $itemData['unit'] ?? 'Pcs')->value('id') ?? 1;
                SppbDetail::create([
                    'sppb_header_id' => $sppb->id,
                    'item_asset_name' => $itemData['item_name'] ?? 'Item',
                    'quantity' => $itemData['qty_requested'] ?? ($itemData['quantity'] ?? 1),
                    'unit_id' => $unitId,
                    'remarks' => $itemData['notes'] ?? ($itemData['remarks'] ?? null),
                    'reference_code' => $itemData['item_code'] ?? null,
                    'delivery_status' => 'PENDING',
                ]);
            }
            $sppb->load('details');
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft SPPB berhasil dibuat.',
            'data' => new SppbResource($sppb),
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ], 201);
    }

    /**
     * Detail SPPB (Mendukung ID, UUID, atau No. Dokumen).
     *
     * Menampilkan informasi detail dokumen SPPB.
     * Catatan Transisi Otomatis: Apabila dokumen berstatus WAITING_VERIFICATION_BAT dan diakses oleh Penyetuju BAT (BAT Approver),
     * sistem akan otomatis memperbarui status menjadi PROCESS_VERIFICATION_BAT dan merekam status log audit BAT_OPENED.
     */
    public function show(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);

        $sppb->markAsBatProcessingIfEligible($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Detail SPPB berhasil ditampilkan.',
            'data' => new SppbResource($sppb),
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    public function update(UpdateSppbRequest $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);

        $requestDate = $sppb->request_date instanceof Carbon
            ? $sppb->request_date->toDateString()
            : ($sppb->request_date ? (string) $sppb->request_date : now()->toDateString());

        $dto = new CreateSppbData(
            plantId: (int) $sppb->plant_id,
            departmentId: (int) $sppb->department_id,
            requesterId: (int) $sppb->requester_id,
            originLocationId: (int) $request->origin_location_id,
            destinationLocationId: (int) $request->destination_location_id,
            requestDate: $requestDate,
            purpose: $request->purpose ?? '',
            neededName: $request->needed_name,
            dateNeeded: $request->date_needed,
            isUrgent: $request->boolean('is_urgent', false)
        );

        $sppb = $this->sppbService->updateDraft($sppb->id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Draft SPPB berhasil diperbarui.',
            'data' => new SppbResource($sppb),
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    public function destroy(Request $request, string $uuid)
    {
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();
        // Soft delete logic can go here (or via SppbService)
        $sppb->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft SPPB berhasil dihapus.',
            'data' => null,
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    public function submit(SubmitSppbRequest $request, string $uuid)
    {
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();

        // Uses SppbService to queue submit
        $command = $this->sppbService->queueSubmit($sppb->id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'SPPB berhasil disubmit dan sedang diproses.',
            'data' => ['command_uuid' => $command->command_uuid],
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    /**
     * Resubmit SPPB setelah direvisi atau ditolak.
     */
    public function resubmit(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);

        $command = $this->sppbService->queueSubmit($sppb->id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'SPPB berhasil diajukan ulang dan sedang diproses.',
            'data' => ['command_uuid' => $command->command_uuid],
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    /**
     * Membatalkan permohonan SPPB (Cancel SPPB).
     * Dapat dilakukan saat DRAFT, REJECTED, atau status cancellable lainnya oleh pemohon/admin.
     */
    public function cancel(Request $request, string $uuid)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $sppb = $this->findSppb($uuid);

        $this->sppbService->cancel($sppb->id, $request->user()->id, $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Permohonan SPPB berhasil dibatalkan.',
            'data' => null,
            'meta' => null,
            'links' => null,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $query = SppbHeader::query();

        // Strict isolation check: non-super_admins can only query their own plant's SPPBs
        if (! $user->hasRole('super_admin') && $user->plant_id) {
            $query->where('plant_id', $user->plant_id);
        }

        $stats = $query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = [
            'DRAFT',
            'WAITING_APPROVAL',
            'APPROVED',
            'RELEASE_IN_PROGRESS',
            'COMPLETED',
            'REVISED',
            'REJECTED',
            'CANCELLED',
        ];

        $data = [];
        foreach ($statuses as $status) {
            $data[strtolower($status)] = $stats[$status] ?? 0;
        }

        $data['total'] = array_sum($data);

        return response()->json([
            'success' => true,
            'message' => 'Statistik SPPB berhasil ditampilkan.',
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function plants(Request $request)
    {
        $plants = Plant::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Pabrik berhasil ditampilkan.',
            'data' => $plants,
        ]);
    }

    public function departments(Request $request)
    {
        $query = Department::where('is_active', true);
        if ($request->has('plant_id')) {
            $query->where('plant_id', $request->query('plant_id'));
        }
        $departments = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Departemen berhasil ditampilkan.',
            'data' => $departments,
        ]);
    }

    public function locations(Request $request)
    {
        $query = Location::where('is_active', true);
        if ($request->has('plant_id')) {
            $query->where('plant_id', $request->query('plant_id'));
        }
        $locations = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi berhasil ditampilkan.',
            'data' => $locations,
        ]);
    }

    public function items(Request $request)
    {
        $query = Item::with('unit')->where('is_active', true);
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->query('search').'%')
                    ->orWhere('code', 'like', '%'.$request->query('search').'%');
            });
        }
        $items = $query->limit($request->query('limit', 50))->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Barang berhasil ditampilkan.',
            'data' => $items,
        ]);
    }

    public function listDetails(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);
        $details = SppbDetail::where('sppb_header_id', $sppb->id)->with('item.unit')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Detail SPPB berhasil ditampilkan.',
            'data' => $details,
        ]);
    }

    public function addDetail(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);

        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $item = Item::findOrFail($request->item_id);

        $dto = new SppbDetailData(
            barcodeConfirmed: false,
            unitId: (int) $item->unit_id,
            quantity: (float) $request->quantity,
            itemId: (int) $request->item_id,
            remarks: $request->remarks
        );

        $detail = $this->sppbService->addDetail($sppb->id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Detail SPPB berhasil ditambahkan.',
            'data' => $detail,
        ], 201);
    }

    public function updateDetail(Request $request, string $uuid, int $detailId)
    {
        $sppb = $this->findSppb($uuid);

        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $detail = SppbDetail::where('id', $detailId)->where('sppb_header_id', $sppb->id)->firstOrFail();
        $item = Item::findOrFail($request->item_id);

        $detail->update([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
            'unit_id' => (int) $item->unit_id,
            'item_asset_name' => $item->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail SPPB berhasil diperbarui.',
            'data' => $detail,
        ]);
    }

    public function removeDetail(Request $request, string $uuid, int $detailId)
    {
        $sppb = $this->findSppb($uuid);
        $this->sppbService->removeDetail($sppb->id, (int) $detailId);

        return response()->json([
            'success' => true,
            'message' => 'Detail SPPB berhasil dihapus.',
        ]);
    }

    public function statusLogs(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);
        $logs = SppbStatusLog::where('sppb_header_id', $sppb->id)
            ->with(['actor.positions.position', 'actor.roles', 'workflowInstanceStep'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Histori status SPPB berhasil ditampilkan.',
            'data' => $logs,
        ]);
    }

    /**
     * Dapatkan sisa kuota barang SPPB yang dapat dilepaskan / dikirim.
     */
    public function releasableItems(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);

        $details = SppbDetail::with(['unit', 'item', 'asset'])
            ->where('sppb_header_id', $sppb->id)
            ->get();

        $items = [];
        foreach ($details as $detail) {
            $released = (float) GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                ->whereHas('goodsRelease', fn ($q) => $q->where('status', '!=', 'CANCELLED'))
                ->sum('quantity_released');

            $requested = (float) $detail->quantity;
            $remaining = max(0.0, $requested - $released);

            $status = $released <= 0
                ? 'PENDING'
                : ($remaining > 0 ? 'PARTIALLY_DELIVERED' : 'DELIVERED');

            $statusLabel = match ($status) {
                'PENDING' => 'Belum Dikirim',
                'PARTIALLY_DELIVERED' => 'Pengiriman Sebagian',
                'DELIVERED' => 'Pengiriman Penuh',
            };

            $items[] = [
                'sppb_detail_id' => $detail->id,
                'line_no' => $detail->line_no,
                'item_id' => $detail->item_id,
                'asset_id' => $detail->asset_id,
                'item_asset_name' => $detail->item_asset_name,
                'reference_code' => $detail->reference_code,
                'unit_id' => $detail->unit_id,
                'unit_name' => $detail->unit?->name,
                'quantity_requested' => $requested,
                'quantity_already_released' => $released,
                'quantity_remaining' => $remaining,
                'delivery_status' => $status,
                'delivery_status_label' => $statusLabel,
                'is_fully_released' => $remaining <= 0,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar sisa kuota barang SPPB berhasil ditampilkan.',
            'data' => [
                'sppb_header_id' => $sppb->id,
                'sppb_uuid' => $sppb->uuid,
                'document_number' => $sppb->document_number,
                'header_status' => $sppb->status,
                'items' => $items,
                'releasable_items' => array_values(array_filter($items, fn ($i) => ! $i['is_fully_released'])),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function findSppb(string $idOrUuid): SppbHeader
    {
        return SppbHeader::query()
            ->where(function ($query) use ($idOrUuid) {
                $query->where('uuid', $idOrUuid)
                    ->orWhere('document_number', $idOrUuid);
                if (is_numeric($idOrUuid)) {
                    $query->orWhere('id', (int) $idOrUuid);
                }
            })
            ->with([
                'plant',
                'department',
                'requester',
                'originLocation',
                'destinationLocation',
                'details.item',
                'details.unit',
                'sppbStatusLogs.actor.positions.position',
                'sppbStatusLogs.actor.roles',
                'sppbStatusLogs.workflowInstanceStep',
            ])
            ->firstOrFail();
    }

    /**
     * Compatibility Route: Setujui Dokumen SPPB via UUID/ID SPPB.
     *
     * POST /api/v1/sppb/{id_or_uuid}/approve
     */
    public function approve(Request $request, string $uuid)
    {
        $sppb = $this->findSppb($uuid);
        $user = $request->user();

        $pendingStep = WorkflowStepApprover::whereHas('workflowInstanceStep.workflowInstance', function ($q) use ($sppb) {
            $q->where('sppb_header_id', $sppb->id);
        })
            ->where('approver_id', $user->id)
            ->where('status', ApproverStatus::PENDING->value)
            ->first();

        if (! $pendingStep) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak persetujuan aktif untuk dokumen SPPB ini.',
            ], 403);
        }

        $remarks = $request->input('remarks') ?? $request->input('notes') ?? $request->input('note');

        $dto = new ApprovalDecisionData(
            workflowInstanceStepId: (int) $pendingStep->workflow_instance_step_id,
            actorId: (int) $user->id,
            commandUuid: (string) Str::uuid(),
            decision: 'approve',
            remarks: $remarks,
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
     * Compatibility Route: Tolak Dokumen SPPB via UUID/ID SPPB.
     *
     * POST /api/v1/sppb/{id_or_uuid}/reject
     */
    public function reject(Request $request, string $uuid)
    {
        $remarks = $request->input('remarks') ?? $request->input('reason') ?? $request->input('notes');
        if (empty($remarks)) {
            return response()->json([
                'success' => false,
                'message' => 'Alasan penolakan (remarks/reason) wajib diisi.',
                'errors' => ['remarks' => ['Field remarks, reason, atau notes wajib diisi.']],
            ], 422);
        }

        $sppb = $this->findSppb($uuid);
        $user = $request->user();

        $pendingStep = WorkflowStepApprover::whereHas('workflowInstanceStep.workflowInstance', function ($q) use ($sppb) {
            $q->where('sppb_header_id', $sppb->id);
        })
            ->where('approver_id', $user->id)
            ->where('status', ApproverStatus::PENDING->value)
            ->first();

        if (! $pendingStep) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak persetujuan aktif untuk dokumen SPPB ini.',
            ], 403);
        }

        $dto = new ApprovalDecisionData(
            workflowInstanceStepId: (int) $pendingStep->workflow_instance_step_id,
            actorId: (int) $user->id,
            commandUuid: (string) Str::uuid(),
            decision: 'reject',
            remarks: (string) $remarks,
            correlationId: (string) Str::uuid()
        );

        $this->workflowService->queueApproval($dto);

        return response()->json([
            'success' => true,
            'message' => 'Penolakan SPPB berhasil diproses.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
