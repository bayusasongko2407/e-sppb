<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentPage;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class DocumentPagePolicy
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
            return $user->hasPermissionTo('view_any_'.strtolower('DocumentPage'));
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentPage $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_page', 'view', $plantId, $departmentId);
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
            if (! $user->hasPermissionTo('create_'.strtolower('DocumentPage'))) {
                return false;
            }
        } catch (PermissionDoesNotExist $e) {
            return false;
        }

        return $user->documentAccesses()
            ->where('module', 'document_page')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentPage $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_page', 'edit', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentPage $model): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $plantId = Schema::hasColumn($model->getTable(), 'plant_id') ? $model->plant_id : null;
        $departmentId = Schema::hasColumn($model->getTable(), 'department_id') ? $model->department_id : null;

        return $user->hasDocumentAccess('document_page', 'delete', $plantId, $departmentId);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentPage $model): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentPage $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
