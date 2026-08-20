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

        $allModules = [
            'sppb',
            'sppb_items',
            'goods_release_search',
            'document_validation_log',
            'sppb_item_fulfillment',
            'item_receipt_discrepancy',
            'asset_movement_history',
        ];

        // Super Admin or users with global permission get unrestricted report access
        if ($user->hasRole('super_admin') || $user->can('view_any_sppbheader')) {
            return new ReportScope(
                allowedModules: $allModules,
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

        if ($accesses->isEmpty()) {
            return new ReportScope(
                allowedModules: $allModules,
                allowedPlants: $user->plant_id ? [$user->plant_id] : [],
                allowedDepartments: $user->department_id ? [$user->department_id] : [],
                canPreview: true,
                canExport: true,
                canPrint: true
            );
        }

        $modules = $accesses->pluck('module')->unique()->filter()->values()->toArray();
        if (in_array('sppb', $modules, true) || in_array('goods_release', $modules, true) || in_array('asset', $modules, true)) {
            $modules = array_unique(array_merge($modules, $allModules));
        }

        $hasWildcardPlant = $accesses->contains(fn ($acc) => is_null($acc->plant_id));
        $allowedPlants = $hasWildcardPlant
            ? []
            : $accesses->pluck('plant_id')->unique()->filter()->values()->toArray();

        if (! $hasWildcardPlant && empty($allowedPlants) && $user->plant_id) {
            $allowedPlants = [$user->plant_id];
        }

        $hasWildcardDept = $accesses->contains(fn ($acc) => is_null($acc->department_id));
        $allowedDepartments = $hasWildcardDept
            ? []
            : $accesses->pluck('department_id')->unique()->filter()->values()->toArray();

        if (! $hasWildcardDept && empty($allowedDepartments) && $user->department_id) {
            $allowedDepartments = [$user->department_id];
        }

        $canView = $accesses->where('can_view', true)->isNotEmpty() || $user->plant_id !== null;

        return new ReportScope(
            allowedModules: array_values($modules),
            allowedPlants: array_values($allowedPlants),
            allowedDepartments: array_values($allowedDepartments),
            canPreview: $canView,
            canExport: $canView,
            canPrint: $canView
        );
    }
}
