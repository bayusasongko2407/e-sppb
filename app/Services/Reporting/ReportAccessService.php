<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\DTOs\Reporting\ReportScope;
use App\Models\DocumentAccess;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportAccessService
{
    /**
     * Build the ReportScope for a given user.
     * If no user is provided, the currently authenticated user is used.
     */
    public function getScopeForUser(?User $user = null): ReportScope
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return new ReportScope([], [], [], false, false, false);
        }

        // Super Admin or users with global permission get unrestricted report access
        if ($user->hasRole('super_admin') || $user->can('view_any_sppbheader')) {
            return new ReportScope(
                allowedModules: ['sppb', 'sppb_items', 'goods_release'],
                allowedPlants: [],
                allowedDepartments: [],
                canPreview: true,
                canExport: true,
                canPrint: true
            );
        }

        $roleIds = $user->roles()->pluck('id')->toArray();

        $accesses = DocumentAccess::where('user_id', $user->id)
            ->when(! empty($roleIds), fn ($q) => $q->orWhereIn('role_id', $roleIds))
            ->get();

        $modules = $accesses->pluck('module')->unique()->filter()->values()->toArray();
        if (in_array('sppb', $modules, true) && ! in_array('sppb_items', $modules, true)) {
            $modules[] = 'sppb_items';
        }

        $hasWildcardPlant = $accesses->contains(fn ($acc) => is_null($acc->plant_id));
        $allowedPlants = $hasWildcardPlant
            ? []
            : $accesses->pluck('plant_id')->unique()->filter()->values()->toArray();

        $hasWildcardDept = $accesses->contains(fn ($acc) => is_null($acc->department_id));
        $allowedDepartments = $hasWildcardDept
            ? []
            : $accesses->pluck('department_id')->unique()->filter()->values()->toArray();

        $canView = $accesses->where('can_view', true)->isNotEmpty();

        return new ReportScope(
            allowedModules: $modules,
            allowedPlants: $allowedPlants,
            allowedDepartments: $allowedDepartments,
            canPreview: $canView,
            canExport: $canView,
            canPrint: $canView
        );
    }
}
