<?php

namespace App\Policies;

use App\Models\GoodsRelease;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GoodsReleasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['super_admin', 'gudang'])) {
            return true;
        }

        try {
            return $user->hasPermissionTo('view_any_goodsrelease');
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoodsRelease $goodsRelease): bool
    {
        if ($user->hasRole(['super_admin', 'gudang'])) {
            return true;
        }

        $sppb = $goodsRelease->sppbHeader;
        if (! $sppb) {
            return false;
        }

        // Requester of the original SPPB can view the release
        if ($sppb->requester_id === $user->id) {
            return true;
        }

        return $user->hasDocumentAccess('goods_release', 'view', $sppb->plant_id, $sppb->department_id);
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
            if (! $user->hasPermissionTo('create_goodsrelease')) {
                return false;
            }
        } catch (PermissionDoesNotExist $e) {
            return false;
        }

        return $user->documentAccesses()
            ->where('module', 'goods_release')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoodsRelease $goodsRelease): bool
    {
        if ($goodsRelease->status !== 'DRAFT') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $sppb = $goodsRelease->sppbHeader;
        if (! $sppb) {
            return false;
        }

        return $user->hasDocumentAccess('goods_release', 'edit', $sppb->plant_id, $sppb->department_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GoodsRelease $goodsRelease): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $sppb = $goodsRelease->sppbHeader;
        if (! $sppb) {
            return false;
        }

        return $user->hasDocumentAccess('goods_release', 'delete', $sppb->plant_id, $sppb->department_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GoodsRelease $goodsRelease): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GoodsRelease $goodsRelease): bool
    {
        return $user->hasRole('super_admin');
    }
}
