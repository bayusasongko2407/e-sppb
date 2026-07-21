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

        $accesses = DocumentAccess::where('user_id', $user->id)
            ->orWhere('role_id', $user->roles()->first()?->id) // Assuming Spatie roles
            ->get();

        $allowedModules = $accesses->pluck('module')->unique()->filter()->values()->toArray();
        $allowedPlants = $accesses->pluck('plant_id')->unique()->filter()->values()->toArray();
        $allowedDepartments = $accesses->pluck('department_id')->unique()->filter()->values()->toArray();

        $canView = $accesses->where('can_view', true)->isNotEmpty();

        // canExport could be mapped to can_view or a specific permission if exists, using can_view as base
        // In the system, report export/print often follows can_view or can_create. Assuming can_view is sufficient for preview/export.
        $canPreview = $canView;
        $canExport = $canView;
        $canPrint = $canView;

        return new ReportScope(
            allowedModules: $allowedModules,
            allowedPlants: $allowedPlants,
            allowedDepartments: $allowedDepartments,
            canPreview: $canPreview,
            canExport: $canExport,
            canPrint: $canPrint
        );
    }
}
