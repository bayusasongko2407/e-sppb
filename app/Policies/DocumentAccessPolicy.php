<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentAccess;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class DocumentAccessPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_any_documentaccess');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function view(User $user, DocumentAccess $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_documentaccess');
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
            return $user->hasPermissionTo('create_documentaccess');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function update(User $user, DocumentAccess $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('update_documentaccess');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function delete(User $user, DocumentAccess $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('delete_documentaccess');
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }

    public function restore(User $user, DocumentAccess $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, DocumentAccess $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
