<?php

namespace App\Policies;

use App\Models\SppbHeader;
use App\Models\User;

class SppbHeaderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_sppbheader');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasPermissionTo('view_sppbheader');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_sppbheader');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasPermissionTo('update_sppbheader');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasPermissionTo('delete_sppbheader');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasPermissionTo('restore_sppbheader');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasPermissionTo('force_delete_sppbheader');
    }
}
