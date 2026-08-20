<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Approver;

use App\Models\SppbHeader;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class ApproverStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->can('viewAny', SppbHeader::class);
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        try {
            $pendingApproval = WorkflowStepApprover::where('approver_id', $userId)
                ->where('status', 'PENDING')
                ->count();

            $overdueApproval = WorkflowInstanceStep::where('status', 'PENDING')
                ->where('due_at', '<', now())
                ->whereHas('stepApprovers', function ($query) use ($userId) {
                    $query->where('approver_id', $userId);
                })
                ->count();

            $approvedToday = WorkflowStepApprover::where('approver_id', $userId)
                ->where('status', 'APPROVED')
                ->whereDate('acted_at', today())
                ->count();

            $rejectedToday = WorkflowStepApprover::where('approver_id', $userId)
                ->where('status', 'REJECTED')
                ->whereDate('acted_at', today())
                ->count();

            return [
                Stat::make('Menunggu Persetujuan Saya', $pendingApproval)
                    ->description('Butuh tindakan segera')
                    ->color('warning')
                    ->icon('heroicon-o-hand-raised'),
                Stat::make('Terlambat (SLA)', $overdueApproval)
                    ->description('Melewati batas waktu')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle'),
                Stat::make('Disetujui Hari Ini', $approvedToday)
                    ->description('Tugas selesai')
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),
                Stat::make('Ditolak Hari Ini', $rejectedToday)
                    ->description('SPPB dikembalikan')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle'),
            ];
        } catch (\Exception $e) {
            Log::error('ApproverStatsWidget error: '.$e->getMessage());

            return [];
        }
    }
}
