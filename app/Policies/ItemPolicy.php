<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_item');
    }

    public function view(User $user, Item $model): bool
    {
        return $user->hasPermissionTo('view_item');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_item');
    }

    public function update(User $user, Item $model): bool
    {
        return $user->hasPermissionTo('update_item');
    }

    public function delete(User $user, Item $model): bool
    {
        return $user->hasPermissionTo('delete_item');
    }

    public function restore(User $user, Item $model): bool
    {
        return $user->hasPermissionTo('restore_item');
    }

    public function forceDelete(User $user, Item $model): bool
    {
        return $user->hasPermissionTo('force_delete_item');
    }
}
