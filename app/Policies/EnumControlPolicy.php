<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EnumControl;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class EnumControlPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_any_enumcontrol');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function view(User $user, EnumControl $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_enumcontrol');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('create_enumcontrol');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function update(User $user, EnumControl $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('update_enumcontrol');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function delete(User $user, EnumControl $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('delete_enumcontrol');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function restore(User $user, EnumControl $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, EnumControl $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
