<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowInstanceStep;

class WorkflowInstanceStepPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_workflowinstancestep');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkflowInstanceStep $workflowInstanceStep): bool
    {
        return $user->hasPermissionTo('view_workflowinstancestep');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_workflowinstancestep');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkflowInstanceStep $workflowInstanceStep): bool
    {
        return $user->hasPermissionTo('update_workflowinstancestep');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkflowInstanceStep $workflowInstanceStep): bool
    {
        return $user->hasPermissionTo('delete_workflowinstancestep');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkflowInstanceStep $workflowInstanceStep): bool
    {
        return $user->hasPermissionTo('restore_workflowinstancestep');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkflowInstanceStep $workflowInstanceStep): bool
    {
        return $user->hasPermissionTo('force_delete_workflowinstancestep');
    }
}
