<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SppbServiceContract;
use App\Contracts\WorkflowServiceContract;
use App\DTOs\Sppb\CreateSppbData;
use App\DTOs\Sppb\SppbDetailData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\SppbStatus;
use App\Exceptions\Workflow\SppbNotEditableException;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\WorkflowCommand;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class SppbService implements SppbServiceContract
{
    public function __construct(
        private readonly WorkflowServiceContract $workflowService,
    ) {}

    public function createDraft(CreateSppbData $data): SppbHeader
    {
        return DB::transaction(function () use ($data) {
            if ($data->originLocationId === $data->destinationLocationId) {
                throw new \InvalidArgumentException('Lokasi asal dan tujuan tidak boleh sama.');
            }

            return SppbHeader::create([
                'uuid' => (string) Uuid::uuid4(),
                'plant_id' => $data->plantId,
                'department_id' => $data->departmentId,
                'requester_id' => $data->requesterId,
                'origin_location_id' => $data->originLocationId,
                'destination_location_id' => $data->destinationLocationId,
                'request_date' => $data->requestDate,
                'purpose' => $data->purpose,
                'needed_name' => $data->neededName,
                'date_needed' => $data->dateNeeded,
                'is_urgent' => $data->isUrgent,
                'status' => SppbStatus::DRAFT->value,
                'revision_no' => 0,
                'lock_version' => 0,
            ]);
        });
    }

    public function updateDraft(int $sppbHeaderId, CreateSppbData $data): SppbHeader
    {
        return DB::transaction(function () use ($sppbHeaderId, $data) {
            $header = SppbHeader::lockForUpdate()->findOrFail($sppbHeaderId);

            if (! SppbStatus::from($header->status)->isEditable()) {
                throw new SppbNotEditableException;
            }

            if ($data->originLocationId === $data->destinationLocationId) {
                throw new \InvalidArgumentException('Lokasi asal dan tujuan tidak boleh sama.');
            }

            $header->update([
                'plant_id' => $data->plantId,
                'department_id' => $data->departmentId,
                'origin_location_id' => $data->originLocationId,
                'destination_location_id' => $data->destinationLocationId,
                'request_date' => $data->requestDate,
                'purpose' => $data->purpose,
                'needed_name' => $data->neededName,
                'date_needed' => $data->dateNeeded,
                'is_urgent' => $data->isUrgent,
            ]);

            return $header->refresh();
        });
    }

    public function addDetail(int $sppbHeaderId, SppbDetailData $data): SppbDetail
    {
        return DB::transaction(function () use ($sppbHeaderId, $data) {
            $header = SppbHeader::lockForUpdate()->findOrFail($sppbHeaderId);

            if (! SppbStatus::from($header->status)->isEditable()) {
                throw new SppbNotEditableException;
            }

            $lastLineNo = SppbDetail::where('sppb_header_id', $sppbHeaderId)->max('line_no') ?? 0;

            return SppbDetail::create([
                'sppb_header_id' => $sppbHeaderId,
                'line_no' => $lastLineNo + 1,
                'barcode_confirmed' => $data->barcodeConfirmed,
                'item_id' => $data->barcodeConfirmed ? null : $data->itemId,
                'asset_id' => $data->barcodeConfirmed ? $data->assetId : null,
                'reference_code' => $data->referenceCode,
                'item_asset_name' => $data->itemAssetName,
                'unit_id' => $data->unitId,
                'quantity' => $data->quantity,
                'remarks' => $data->remarks,
            ]);
        });
    }

    public function removeDetail(int $sppbHeaderId, int $detailId): void
    {
        DB::transaction(function () use ($sppbHeaderId, $detailId) {
            $header = SppbHeader::lockForUpdate()->findOrFail($sppbHeaderId);

            if (! SppbStatus::from($header->status)->isEditable()) {
                throw new SppbNotEditableException;
            }

            SppbDetail::where('id', $detailId)
                ->where('sppb_header_id', $sppbHeaderId)
                ->delete();
        });
    }

    public function queueSubmit(int $sppbHeaderId, int $actorId): WorkflowCommand
    {
        SppbHeader::findOrFail($sppbHeaderId);

        if (! SppbDetail::where('sppb_header_id', $sppbHeaderId)->exists()) {
            throw new \InvalidArgumentException('SPPB harus memiliki minimal satu detail barang sebelum diajukan.');
        }

        $commandUuid = (string) Uuid::uuid4();
        $submitData = new SubmitSppbData(
            sppbHeaderId: $sppbHeaderId,
            actorId: $actorId,
            commandUuid: $commandUuid,
            correlationId: (string) Uuid::uuid4(),
        );

        return $this->workflowService->queueSubmission($submitData);
    }

    public function cancel(int $sppbHeaderId, int $actorId, string $reason): void
    {
        $this->workflowService->cancelWorkflow($sppbHeaderId, $actorId, $reason);
    }

    public function getAllowedActions(int $sppbHeaderId, int $actorId): array
    {
        $header = SppbHeader::findOrFail($sppbHeaderId);
        $status = SppbStatus::from($header->status);
        $actions = [];

        if ($status->isEditable()) {
            $actions[] = 'edit';
            $actions[] = 'submit';
        }

        if ($status->isCancellable() && $header->requester_id === $actorId) {
            $actions[] = 'cancel';
        }

        return $actions;
    }
}
