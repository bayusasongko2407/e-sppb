<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowStepApprover;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class WorkflowStepApproverPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_any_'.strtolower('WorkflowStepApprover'));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkflowStepApprover $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('workflow_step_approver', 'view', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            if (! $user->hasPermissionTo('create_'.strtolower('WorkflowStepApprover'))) {
                return false;
            }
        } catch (PermissionDoesNotExist $e) {
            return false;
        }

        return $user->documentAccesses()
            ->where('module', 'workflow_step_approver')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkflowStepApprover $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('workflow_step_approver', 'edit', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkflowStepApprover $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('workflow_step_approver', 'delete', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkflowStepApprover $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkflowStepApprover $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
