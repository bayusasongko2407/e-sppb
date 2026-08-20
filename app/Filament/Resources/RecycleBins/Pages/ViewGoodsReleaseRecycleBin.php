<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Pages;

use App\Filament\Resources\RecycleBins\GoodsReleaseRecycleBinResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsReleaseRecycleBin extends ViewRecord
{
    protected static string $resource = GoodsReleaseRecycleBinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->label('Pulihkan')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Pulihkan Surat Jalan')
                ->modalDescription('Apakah Anda yakin ingin memulihkan dokumen Surat Jalan ini kembali ke daftar utama?'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Hapus Permanen Surat Jalan')
                ->modalDescription('Apakah Anda yakin ingin menghapus permanen dokumen Surat Jalan ini? Data yang dihapus permanen tidak dapat dikembalikan.'),
        ];
    }
}
