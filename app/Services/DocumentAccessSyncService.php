<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class DocumentAccessSyncService
{
    /**
     * Sync Spatie permissions and role relations based on DocumentAccess configuration.
     */
    public static function syncAccessPermissions(?int $userId, ?int $roleId, array $accessItems): void
    {
        $permissionsToGrant = [];

        $moduleMap = [
            'sppb' => ['sppbheader', 'sppbdetail', 'sppbstatuslog'],
            'goods_release' => ['goodsrelease', 'goodsreleaseitem'],
            'asset' => ['asset'],
        ];

        foreach ($accessItems as $item) {
            $moduleKey = $item['module'] ?? null;
            if (! $moduleKey) {
                continue;
            }

            $models = $moduleMap[$moduleKey] ?? [$moduleKey];

            $canView = ! empty($item['can_view']) || ! empty($item['can_create']) || ! empty($item['can_edit']) || ! empty($item['can_delete']);
            $canCreate = ! empty($item['can_create']);
            $canEdit = ! empty($item['can_edit']);
            $canDelete = ! empty($item['can_delete']);

            foreach ($models as $model) {
                if ($canView) {
                    $permissionsToGrant[] = "view_any_{$model}";
                    $permissionsToGrant[] = "view_{$model}";
                }
                if ($canCreate) {
                    $permissionsToGrant[] = "create_{$model}";
                }
                if ($canEdit) {
                    $permissionsToGrant[] = "update_{$model}";
                }
                if ($canDelete) {
                    $permissionsToGrant[] = "delete_{$model}";
                }
            }

            // Custom permission for BAT Verifier / Asset verification
            if ($moduleKey === 'sppb' || $moduleKey === 'asset') {
                if (! empty($item['can_view']) || ! empty($item['can_edit'])) {
                    $permissionsToGrant[] = 'verify_bat';
                }
            }
        }

        $permissionsToGrant = array_unique(array_filter($permissionsToGrant));

        if (empty($permissionsToGrant)) {
            return;
        }

        // Ensure all permissions exist in the database
        foreach ($permissionsToGrant as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 1. If role_id is provided, sync permissions directly to the Role
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $role->givePermissionTo($permissionsToGrant);
            }
        }

        // 2. If user_id is provided, sync permissions to User & User's Roles
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->givePermissionTo($permissionsToGrant);

                // Also sync permissions to user's assigned Spatie roles so Role management UI reflects it
                foreach ($user->roles as $userRole) {
                    $userRole->givePermissionTo($permissionsToGrant);
                }
            }
        }
    }
}
