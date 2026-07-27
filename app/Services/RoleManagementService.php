<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementService
{
    /**
     * @var array<string>
     */
    public const SYSTEM_ROLES = [
        'super_admin',
        'admin',
        'requester',
        'approver',
        'manager',
    ];

    /**
     * Create a new role.
     *
     * @throws Exception
     */
    public function createRole(string $name, ?string $description = null, ?User $actor = null): Role
    {
        return DB::transaction(function () use ($name, $actor) {
            $role = Role::create(['name' => $name, 'guard_name' => 'web']);

            // Description handling can be added if role table has description column,
            // but standard Spatie doesn't have it by default. We'll skip for now.

            $this->logActivity('role', 'create', $role->id, $actor, null, ['name' => $name]);

            return $role;
        });
    }

    /**
     * Update an existing role.
     *
     * @throws Exception
     */
    public function updateRole(Role $role, string $name, ?User $actor = null): Role
    {
        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            throw new Exception('Role sistem tidak dapat diubah namanya.');
        }

        return DB::transaction(function () use ($role, $name, $actor) {
            $oldValues = ['name' => $role->name];
            $role->name = $name;
            $role->save();

            $this->logActivity('role', 'update', $role->id, $actor, $oldValues, ['name' => $name]);

            return $role;
        });
    }

    /**
     * Delete a role.
     *
     * @throws Exception
     */
    public function deleteRole(Role $role, ?User $actor = null): void
    {
        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            throw new Exception('Role sistem tidak dapat dihapus.');
        }

        DB::transaction(function () use ($role, $actor) {
            $oldValues = ['name' => $role->name];
            $roleId = $role->id;

            $role->delete();

            $this->logActivity('role', 'delete', $roleId, $actor, $oldValues, null);
        });
    }

    /**
     * Sync permissions to a role.
     *
     * @param  array<string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions, ?User $actor = null): Role
    {
        return DB::transaction(function () use ($role, $permissions, $actor) {
            $oldPermissions = $role->permissions()->pluck('name')->toArray();

            $role->syncPermissions($permissions);

            $this->logActivity('role', 'sync_permissions', $role->id, $actor, ['permissions' => $oldPermissions], ['permissions' => $permissions]);

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return $role;
        });
    }

    /**
     * Assign role to user.
     *
     * @throws Exception
     */
    public function assignRoleToUser(User $user, string $roleName, ?User $actor = null): User
    {
        return DB::transaction(function () use ($user, $roleName, $actor) {
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
                $this->logActivity('user', 'assign_role', $user->id, $actor, null, ['role' => $roleName]);
            }

            return $user;
        });
    }

    /**
     * Remove role from user.
     *
     * @throws Exception
     */
    public function removeRoleFromUser(User $user, string $roleName, ?User $actor = null): User
    {
        return DB::transaction(function () use ($user, $roleName, $actor) {
            if ($roleName === 'super_admin') {
                $superAdminCount = User::role('super_admin')->count();
                if ($superAdminCount <= 1 && $user->hasRole('super_admin')) {
                    throw new Exception('Tidak dapat mencabut akses super admin terakhir.');
                }
            }

            if ($user->hasRole($roleName)) {
                $user->removeRole($roleName);
                $this->logActivity('user', 'remove_role', $user->id, $actor, ['role' => $roleName], null);
            }

            return $user;
        });
    }

    /**
     * Clear permission cache.
     */
    public function clearPermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Write activity log.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function logActivity(
        string $module,
        string $action,
        int $subjectId,
        ?User $actor,
        ?array $oldValues,
        ?array $newValues
    ): void {
        ActivityLog::create([
            'actor_id' => $actor?->id,
            'module' => $module,
            'action' => $action,
            'subject_type' => $module,
            'subject_id' => $subjectId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
