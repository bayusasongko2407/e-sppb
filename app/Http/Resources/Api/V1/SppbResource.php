<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\ApproverStatus;
use App\Models\WorkflowStepApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SppbResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $requesterName = $this->requester?->name ?? $this->needed_name ?? null;
        $destinationName = $this->destinationLocation?->name ?? null;
        $departmentName = $this->department?->name ?? null;

        $data['requester_name'] = $requesterName;
        $data['needed_name'] = $this->needed_name ?? $requesterName;
        $data['requester'] = $this->requester ? [
            'id' => $this->requester->id,
            'name' => $this->requester->name,
            'nik' => $this->requester->nik ?? '',
            'email' => $this->requester->email ?? '',
        ] : null;

        $data['destination_location_name'] = $destinationName;
        $data['destination_name'] = $destinationName;
        $data['destination_location'] = $this->destinationLocation ? [
            'id' => $this->destinationLocation->id,
            'code' => $this->destinationLocation->code ?? '',
            'name' => $this->destinationLocation->name,
        ] : null;

        $data['department_name'] = $departmentName;
        $data['department'] = $this->department ? [
            'id' => $this->department->id,
            'code' => $this->department->code ?? '',
            'name' => $this->department->name,
        ] : null;

        $user = $request->user();
        if ($user) {
            $pendingStep = WorkflowStepApprover::whereHas('workflowInstanceStep.workflowInstance', function ($q) {
                $q->where('sppb_header_id', $this->id);
            })
                ->where('approver_id', $user->id)
                ->where('status', ApproverStatus::PENDING->value)
                ->first();

            $data['current_user_pending_step_id'] = $pendingStep ? $pendingStep->workflow_instance_step_id : null;
        } else {
            $data['current_user_pending_step_id'] = null;
        }

        return $data;
    }
}
