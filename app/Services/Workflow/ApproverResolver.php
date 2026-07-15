<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Exceptions\Workflow\ApproverNotFoundException;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Support\Collection;

final class ApproverResolver
{
    /**
     * Resolve approver candidates untuk satu workflow step.
     *
     * @return Collection<int, User>
     */
    public function resolve(WorkflowStep $step, SppbHeader $header): Collection
    {
        $candidates = match ($step->approver_type) {
            'USER' => $this->resolveByUser($step),
            'ROLE' => $this->resolveByRole($step, $header),
            'POSITION' => $this->resolveByPosition($step, $header),
            'REQUESTER_MANAGER' => $this->resolveRequesterManager($header),
            'DEPARTMENT_HEAD' => $this->resolveByRole($step, $header, forDeptHead: true),
            default => collect(),
        };

        $candidates = $candidates->where('is_active', true);

        // Filter scope plant
        if ($header->plant_id) {
            $candidates = $candidates->where('plant_id', $header->plant_id);
        }

        if ($candidates->isEmpty()) {
            throw new ApproverNotFoundException;
        }

        return $candidates->values();
    }

    private function resolveByUser(WorkflowStep $step): Collection
    {
        if (empty($step->approver_user_ids)) {
            return collect();
        }

        return User::whereIn('id', $step->approver_user_ids)->get();
    }

    private function resolveByRole(WorkflowStep $step, SppbHeader $header, bool $forDeptHead = false): Collection
    {
        $roleName = $forDeptHead ? 'manager_approver' : $step->approver_role;
        if (! $roleName) {
            return collect();
        }

        return User::role($roleName)
            ->where('plant_id', $header->plant_id)
            ->get();
    }

    private function resolveByPosition(WorkflowStep $step, SppbHeader $header): Collection
    {
        if (empty($step->approver_position_ids)) {
            return collect();
        }

        return User::whereHas('positions', fn ($q) => $q->whereIn('position_id', $step->approver_position_ids)
            ->where('is_active', true)
        )->where('plant_id', $header->plant_id)->get();
    }

    private function resolveRequesterManager(SppbHeader $header): Collection
    {
        $requester = User::find($header->requester_id);
        if (! $requester?->manager_id) {
            return collect();
        }

        return User::where('id', $requester->manager_id)->get();
    }
}
