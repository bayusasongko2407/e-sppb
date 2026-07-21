<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApiSetting;
use App\Models\User;

class ApiSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_apisetting');
    }

    public function view(User $user, ApiSetting $model): bool
    {
        return $user->hasPermissionTo('view_apisetting');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_apisetting');
    }

    public function update(User $user, ApiSetting $model): bool
    {
        return $user->hasPermissionTo('update_apisetting');
    }

    public function delete(User $user, ApiSetting $model): bool
    {
        return $user->hasPermissionTo('delete_apisetting');
    }

    public function restore(User $user, ApiSetting $model): bool
    {
        return $user->hasPermissionTo('restore_apisetting');
    }

    public function forceDelete(User $user, ApiSetting $model): bool
    {
        return $user->hasPermissionTo('force_delete_apisetting');
    }
}
