<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentAccess;
use App\Models\User;

class DocumentAccessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_documentaccess');
    }

    public function view(User $user, DocumentAccess $model): bool
    {
        return $user->hasPermissionTo('view_documentaccess');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_documentaccess');
    }

    public function update(User $user, DocumentAccess $model): bool
    {
        return $user->hasPermissionTo('update_documentaccess');
    }

    public function delete(User $user, DocumentAccess $model): bool
    {
        return $user->hasPermissionTo('delete_documentaccess');
    }

    public function restore(User $user, DocumentAccess $model): bool
    {
        return $user->hasPermissionTo('restore_documentaccess');
    }

    public function forceDelete(User $user, DocumentAccess $model): bool
    {
        return $user->hasPermissionTo('force_delete_documentaccess');
    }
}
