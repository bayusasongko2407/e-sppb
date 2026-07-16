<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Admin;

use App\Models\Asset;
use App\Models\GoodsRelease;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class AssetStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        try {
            $activeAssets = Asset::where('is_active', true)->count();
            $inactiveAssets = Asset::where('is_active', false)->count();

            $releasedToday = GoodsRelease::whereDate('released_at', Carbon::today())
                ->orWhereDate('created_at', Carbon::today())
                ->where('status', 'RELEASED')
                ->count();

            $releasedThisMonth = GoodsRelease::whereMonth('created_at', Carbon::now()->month)
                ->where('status', 'RELEASED')
                ->count();

            return [
                Stat::make('Total Aset Aktif', $activeAssets)
                    ->description('Tersedia / Dapat dipinjam')
                    ->color('success')
                    ->icon('heroicon-o-computer-desktop'),
                Stat::make('Aset Tidak Aktif', $inactiveAssets)
                    ->description('Rusak / Perbaikan')
                    ->color('danger')
                    ->icon('heroicon-o-wrench-screwdriver'),
                Stat::make('Barang Keluar Hari Ini', $releasedToday)
                    ->description('Berdasarkan SPPB')
                    ->color('warning')
                    ->icon('heroicon-o-truck'),
                Stat::make('Total Keluar Bulan Ini', $releasedThisMonth)
                    ->description('Akumulasi pengeluaran')
                    ->color('primary')
                    ->icon('heroicon-o-cube-transparent'),
            ];
        } catch (\Exception $e) {
            Log::error('AssetStatsWidget error: '.$e->getMessage());

            return [];
        }
    }
}
