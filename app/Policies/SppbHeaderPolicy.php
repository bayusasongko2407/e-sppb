<?php

namespace App\Policies;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use App\Models\User;

class SppbHeaderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasDocumentAccess('sppb', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SppbHeader $sppbHeader): bool
    {
        if ($sppbHeader->trashed()) {
            return $user->hasRole('super_admin');
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Requester can view their own SPPB
        if ($sppbHeader->requester_id === $user->id) {
            return true;
        }

        // Current approver can view
        if ($sppbHeader->current_approver_id === $user->id) {
            return true;
        }

        return $user->hasDocumentAccess('sppb', 'view', $sppbHeader->plant_id, $sppbHeader->department_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasDocumentAccess('sppb', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SppbHeader $sppbHeader): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! in_array($sppbHeader->status, [SppbStatus::DRAFT->value, SppbStatus::REJECTED->value])) {
            return false;
        }

        if ($sppbHeader->requester_id === $user->id) {
            return true;
        }

        return $user->hasDocumentAccess('sppb', 'edit', $sppbHeader->plant_id, $sppbHeader->department_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SppbHeader $sppbHeader): bool
    {
        return $user->hasRole('super_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
