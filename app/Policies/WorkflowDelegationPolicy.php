<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowDelegation;

class WorkflowDelegationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_workflowdelegation');
    }

    public function view(User $user, WorkflowDelegation $model): bool
    {
        return $user->hasPermissionTo('view_workflowdelegation');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_workflowdelegation');
    }

    public function update(User $user, WorkflowDelegation $model): bool
    {
        return $user->hasPermissionTo('update_workflowdelegation');
    }

    public function delete(User $user, WorkflowDelegation $model): bool
    {
        return $user->hasPermissionTo('delete_workflowdelegation');
    }

    public function restore(User $user, WorkflowDelegation $model): bool
    {
        return $user->hasPermissionTo('restore_workflowdelegation');
    }

    public function forceDelete(User $user, WorkflowDelegation $model): bool
    {
        return $user->hasPermissionTo('force_delete_workflowdelegation');
    }
}
