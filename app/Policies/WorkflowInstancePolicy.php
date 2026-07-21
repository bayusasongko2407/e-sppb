<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowInstance;

class WorkflowInstancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_workflowinstance');
    }

    public function view(User $user, WorkflowInstance $model): bool
    {
        return $user->hasPermissionTo('view_workflowinstance');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_workflowinstance');
    }

    public function update(User $user, WorkflowInstance $model): bool
    {
        return $user->hasPermissionTo('update_workflowinstance');
    }

    public function delete(User $user, WorkflowInstance $model): bool
    {
        return $user->hasPermissionTo('delete_workflowinstance');
    }

    public function restore(User $user, WorkflowInstance $model): bool
    {
        return $user->hasPermissionTo('restore_workflowinstance');
    }

    public function forceDelete(User $user, WorkflowInstance $model): bool
    {
        return $user->hasPermissionTo('force_delete_workflowinstance');
    }
}
