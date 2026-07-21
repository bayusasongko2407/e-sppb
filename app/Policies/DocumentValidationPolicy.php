<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentValidation;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class DocumentValidationPolicy
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
            return $user->hasPermissionTo('view_any_'.strtolower('DocumentValidation'));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentValidation $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_validation', 'view', $plantId, $departmentId);
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
            if (! $user->hasPermissionTo('create_'.strtolower('DocumentValidation'))) {
                return false;
            }
        } catch (PermissionDoesNotExist $e) {
            return false;
        }

        return $user->documentAccesses()
            ->where('module', 'document_validation')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentValidation $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_validation', 'edit', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentValidation $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_validation', 'delete', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentValidation $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentValidation $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
