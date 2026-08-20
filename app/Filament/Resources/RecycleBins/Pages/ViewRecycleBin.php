<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Pages;

use App\Filament\Resources\RecycleBins\RecycleBinResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRecycleBin extends ViewRecord
{
    protected static string $resource = RecycleBinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->label('Pulihkan')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Pulihkan Dokumen SPPB')
                ->modalDescription('Apakah Anda yakin ingin memulihkan dokumen SPPB ini kembali ke daftar utama?')
                ->successRedirectUrl(RecycleBinResource::getUrl('index')),

            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Hapus Permanen Dokumen SPPB')
                ->modalDescription('Apakah Anda yakin ingin menghapus permanen dokumen ini? Data yang dihapus permanen tidak dapat dikembalikan.')
                ->successRedirectUrl(RecycleBinResource::getUrl('index')),
        ];
    }
}
