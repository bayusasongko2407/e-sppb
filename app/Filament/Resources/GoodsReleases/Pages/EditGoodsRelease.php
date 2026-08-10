<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGoodsRelease extends EditRecord
{
    protected static string $resource = GoodsReleaseResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Action::make('print_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('goods-releases.preview', $record))
                ->openUrlInNewTab()
                ->visible(fn () => $record && $record->status !== 'DRAFT'),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        $record = $this->getRecord();

        if ($record && $record->status !== 'DRAFT') {
            return [];
        }

        return [
            Action::make('saveDraft')
                ->label('Simpan Sebagai Draft')
                ->action(function () {
                    $this->data['status'] = 'DRAFT';
                    $this->save();
                })
                ->color('gray'),

            Action::make('saveFinal')
                ->label('Simpan Final')
                ->requiresConfirmation()
                ->modalHeading('Simpan Final Surat Jalan')
                ->modalDescription('Apakah Anda yakin? Setelah disimpan final, data Surat Jalan tidak dapat diubah kembali.')
                ->action(function () {
                    $this->data['status'] = 'RELEASED';
                    $this->save();
                })
                ->color('primary'),

            $this->getCancelFormAction(),
        ];
    }
}
