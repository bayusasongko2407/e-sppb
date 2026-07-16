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
            'ROLE' => $this->resolveByRole($step),
            'POSITION' => $this->resolveByPosition($step),
            'REQUESTER_MANAGER' => $this->resolveRequesterManager($header),
            'DEPARTMENT_HEAD' => $this->resolveByRole($step, forDeptHead: true),
            default => collect(),
        };

        $candidates = $candidates->where('is_active', true);

        // Filter scope plant berdasarkan document_access, jika tidak ada, fallback ke user plant_id
        if ($header->plant_id) {
            $module = strtolower($header->document_type ?? 'sppb');
            $candidates = $candidates->filter(function ($user) use ($header, $module) {
                if ($user->hasRole('super_admin')) {
                    return true;
                }

                // Jika ada konfigurasi document_access, patuhi aturan tersebut
                if ($user->documentAccesses()->where('module', $module)->exists()) {
                    return $user->hasDocumentAccess($module, 'view', $header->plant_id);
                }

                // Fallback jika tidak ada konfigurasi document_access sama sekali
                return $user->plant_id === $header->plant_id;
            });
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

    private function resolveByRole(WorkflowStep $step, bool $forDeptHead = false): Collection
    {
        $roleName = $forDeptHead ? 'manager' : $step->approver_role;
        if (! $roleName) {
            return collect();
        }

        return User::role($roleName)->get();
    }

    private function resolveByPosition(WorkflowStep $step): Collection
    {
        if (empty($step->approver_position_ids)) {
            return collect();
        }

        return User::whereHas('positions', fn ($q) => $q->whereIn('position_id', $step->approver_position_ids)
            ->where('is_active', true)
        )->get();
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
