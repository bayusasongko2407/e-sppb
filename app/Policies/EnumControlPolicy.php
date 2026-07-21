<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EnumControl;
use App\Models\User;

class EnumControlPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_enumcontrol');
    }

    public function view(User $user, EnumControl $model): bool
    {
        return $user->hasPermissionTo('view_enumcontrol');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_enumcontrol');
    }

    public function update(User $user, EnumControl $model): bool
    {
        return $user->hasPermissionTo('update_enumcontrol');
    }

    public function delete(User $user, EnumControl $model): bool
    {
        return $user->hasPermissionTo('delete_enumcontrol');
    }

    public function restore(User $user, EnumControl $model): bool
    {
        return $user->hasPermissionTo('restore_enumcontrol');
    }

    public function forceDelete(User $user, EnumControl $model): bool
    {
        return $user->hasPermissionTo('force_delete_enumcontrol');
    }
}
