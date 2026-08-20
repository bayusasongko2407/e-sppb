<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Manager;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagerKpiWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

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
        try {
            $user = auth()->user();
            $startOfMonth = Carbon::now()->startOfMonth();

            $baseQuery = SppbHeader::query();

            if ($user && ! $user->hasRole('super_admin')) {
                $baseQuery->whereExists(function ($rawQuery) use ($user) {
                    $rawQuery->select(DB::raw(1))
                        ->from('document_accesses')
                        ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                        ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                        ->where('document_accesses.user_id', $user->id)
                        ->where('document_accesses.module', 'sppb')
                        ->where('document_accesses.can_view', true);
                });
            }

            $totalMonth = (clone $baseQuery)->where('created_at', '>=', $startOfMonth)->count();

            $inApproval = (clone $baseQuery)->where('status', SppbStatus::WAITING_APPROVAL->value)->count();

            $completedMonth = (clone $baseQuery)->where('updated_at', '>=', $startOfMonth)
                ->where('status', SppbStatus::COMPLETED->value)
                ->count();

            $rejectedMonth = (clone $baseQuery)->where('updated_at', '>=', $startOfMonth)
                ->where('status', SppbStatus::REJECTED->value)
                ->count();

            $approvedCount = (clone $baseQuery)->where('updated_at', '>=', $startOfMonth)
                ->whereIn('status', [SppbStatus::APPROVED->value, SppbStatus::RELEASE_IN_PROGRESS->value, SppbStatus::COMPLETED->value])
                ->count();

            $approvalRate = $totalMonth > 0 ? round(($approvedCount / $totalMonth) * 100, 1) : 0;

            return [
                Stat::make('Total SPPB Bulan Ini', $totalMonth)
                    ->description('Permintaan baru')
                    ->color('primary')
                    ->icon('heroicon-o-document-duplicate'),
                Stat::make('Sedang Approval', $inApproval)
                    ->description('Menunggu persetujuan')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path'),
                Stat::make('Selesai Bulan Ini', $completedMonth)
                    ->description('Barang telah selesai diambil')
                    ->color('success')
                    ->icon('heroicon-o-check-badge'),
                Stat::make('Approval Rate', $approvalRate.'%')
                    ->description('Tingkat persetujuan')
                    ->color('info')
                    ->icon('heroicon-o-chart-pie'),
            ];
        } catch (\Exception $e) {
            Log::error('ManagerKpiWidget error: '.$e->getMessage());

            return [];
        }
    }
}
