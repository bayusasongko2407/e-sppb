<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Requester;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class RequesterStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->can('create', SppbHeader::class);
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        try {
            $draftCount = SppbHeader::where('requester_id', $userId)
                ->where('status', SppbStatus::DRAFT->value)
                ->count();

            $pendingCount = SppbHeader::where('requester_id', $userId)
                ->whereIn('status', [SppbStatus::WAITING_APPROVAL->value, SppbStatus::SUBMISSION_QUEUED->value])
                ->count();

            $approvedCount = SppbHeader::where('requester_id', $userId)
                ->whereIn('status', [SppbStatus::APPROVED->value, SppbStatus::COMPLETED->value, SppbStatus::RELEASE_IN_PROGRESS->value])
                ->count();

            $rejectedCount = SppbHeader::where('requester_id', $userId)
                ->where('status', SppbStatus::REJECTED->value)
                ->count();

            return [
                Stat::make('SPPB Draft', $draftCount)
                    ->description('Belum diajukan')
                    ->color('gray')
                    ->icon('heroicon-o-pencil-square'),
                Stat::make('Menunggu Persetujuan', $pendingCount)
                    ->description('Dalam proses approval')
                    ->color('warning')
                    ->icon('heroicon-o-clock'),
                Stat::make('Disetujui', $approvedCount)
                    ->description('Siap untuk diambil')
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),
                Stat::make('Ditolak', $rejectedCount)
                    ->description('Perlu revisi atau dibatalkan')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle'),
            ];
        } catch (\Exception $e) {
            Log::error('RequesterStatsWidget error: '.$e->getMessage());

            return [];
        }
    }
}
