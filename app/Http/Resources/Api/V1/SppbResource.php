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
