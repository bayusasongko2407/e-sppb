<?php

namespace App\Policies;

use App\Models\Plant;
use App\Models\User;

class PlantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_plant');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Plant $plant): bool
    {
        return $user->hasPermissionTo('view_plant');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_plant');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plant $plant): bool
    {
        return $user->hasPermissionTo('update_plant');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plant $plant): bool
    {
        if ($plant->hasDependentRecords()) {
            return false;
        }

        return $user->hasPermissionTo('delete_plant');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plant $plant): bool
    {
        return $user->hasPermissionTo('restore_plant');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plant $plant): bool
    {
        if ($plant->hasDependentRecords()) {
            return false;
        }

        return $user->hasPermissionTo('force_delete_plant');
    }
}
