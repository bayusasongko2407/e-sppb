<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GoodsRelease;
use App\Models\SppbHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardMetricsController extends Controller
{
    /**
     * Get dashboard metrics overview for E-SPPB Mobile.
     */
    public function metrics(Request $request): JsonResponse
    {
        $totalSppb = SppbHeader::count();
        $pendingApprovals = SppbHeader::whereIn('status', ['SUBMITTED', 'WAITING_APPROVAL', 'PROCESS_VERIFICATION_BAT'])->count();
        $readyForRelease = SppbHeader::where('status', 'APPROVED')->count();
        $completedToday = GoodsRelease::whereDate('created_at', today())->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_sppb' => $totalSppb,
                'pending_approvals' => $pendingApprovals,
                'ready_for_release' => $readyForRelease,
                'completed_today' => $completedToday,
                'critical_alerts' => 0,
            ],
        ]);
    }
}
