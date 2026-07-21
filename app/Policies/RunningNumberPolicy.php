<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RunningNumber;
use App\Models\User;

class RunningNumberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_runningnumber');
    }

    public function view(User $user, RunningNumber $model): bool
    {
        return $user->hasPermissionTo('view_runningnumber');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_runningnumber');
    }

    public function update(User $user, RunningNumber $model): bool
    {
        return $user->hasPermissionTo('update_runningnumber');
    }

    public function delete(User $user, RunningNumber $model): bool
    {
        return $user->hasPermissionTo('delete_runningnumber');
    }

    public function restore(User $user, RunningNumber $model): bool
    {
        return $user->hasPermissionTo('restore_runningnumber');
    }

    public function forceDelete(User $user, RunningNumber $model): bool
    {
        return $user->hasPermissionTo('force_delete_runningnumber');
    }
}
