<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowTemplate;

class WorkflowTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_workflowtemplate');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasPermissionTo('view_workflowtemplate');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_workflowtemplate');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasPermissionTo('update_workflowtemplate');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasPermissionTo('delete_workflowtemplate');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasPermissionTo('restore_workflowtemplate');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasPermissionTo('force_delete_workflowtemplate');
    }
}
