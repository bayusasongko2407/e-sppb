<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SppbDetail;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class SppbDetailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_any_'.strtolower('SppbDetail'));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SppbDetail $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('sppb_detail', 'view', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            if (! $user->hasPermissionTo('create_'.strtolower('SppbDetail'))) {
                return false;
            }
        } catch (PermissionDoesNotExist $e) {
            return false;
        }

        return $user->documentAccesses()
            ->where('module', 'sppb_detail')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SppbDetail $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('sppb_detail', 'edit', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SppbDetail $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('sppb_detail', 'delete', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SppbDetail $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SppbDetail $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
