<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Sppb\CreateSppbData;
use App\DTOs\Sppb\SppbDetailData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Sppb\CreateSppbRequest;
use App\Http\Requests\Api\V1\Sppb\SubmitSppbRequest;
use App\Http\Requests\Api\V1\Sppb\UpdateSppbRequest;
use App\Http\Resources\Api\V1\SppbResource;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\SppbStatusLog;
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
            $query->where('status', $request->query('status'));
        }
        if ($request->has('plant_id')) {
            $query->where('plant_id', $request->query('plant_id'));
        }

        $sppbs = $query->paginate($request->query('per_page', 15));

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

    public function show(Request $request, string $uuid)
    {
        $sppb = SppbHeader::where('uuid', $uuid)
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
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();

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
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();
        $details = SppbDetail::where('sppb_header_id', $sppb->id)->with('item.unit')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Detail SPPB berhasil ditampilkan.',
            'data' => $details,
        ]);
    }

    public function addDetail(Request $request, string $uuid)
    {
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();

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
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();

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
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();
        $this->sppbService->removeDetail($sppb->id, (int) $detailId);

        return response()->json([
            'success' => true,
            'message' => 'Detail SPPB berhasil dihapus.',
        ]);
    }

    public function statusLogs(Request $request, string $uuid)
    {
        $sppb = SppbHeader::where('uuid', $uuid)->firstOrFail();
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
}
