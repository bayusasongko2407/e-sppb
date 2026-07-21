<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_position');
    }

    public function view(User $user, Position $model): bool
    {
        return $user->hasPermissionTo('view_position');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_position');
    }

    public function update(User $user, Position $model): bool
    {
        return $user->hasPermissionTo('update_position');
    }

    public function delete(User $user, Position $model): bool
    {
        return $user->hasPermissionTo('delete_position');
    }

    public function restore(User $user, Position $model): bool
    {
        return $user->hasPermissionTo('restore_position');
    }

    public function forceDelete(User $user, Position $model): bool
    {
        return $user->hasPermissionTo('force_delete_position');
    }
}
