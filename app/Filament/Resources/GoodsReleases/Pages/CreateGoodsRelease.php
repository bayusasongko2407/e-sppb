<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsRelease extends CreateRecord
{
    protected static string $resource = GoodsReleaseResource::class;

    protected function afterCreate(): void
    {
        $goodsRelease = $this->record;
        if ($goodsRelease && $goodsRelease->sppbHeader && $goodsRelease->sppbHeader->requester) {
            $header = $goodsRelease->sppbHeader;
            $statusText = $goodsRelease->status === 'RELEASED' ? 'telah diterbitkan (Final)' : 'telah dibuat (Draft)';
            Notification::make()
                ->title('Update Pengeluaran Barang')
                ->body("Surat Jalan #{$goodsRelease->release_number} untuk SPPB {$header->document_number} {$statusText}.")
                ->icon('heroicon-o-truck')
                ->actions([
                    Action::make('view')
                        ->label('Lihat Detail')
                        ->url(SppbHeaderResource::getUrl('view', ['record' => $header])),
                ])
                ->sendToDatabase($header->requester, isEventDispatched: true);
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saveDraft')
                ->label('Simpan Sebagai Draft')
                ->action(function () {
                    $this->data['status'] = 'DRAFT';
                    $this->create();
                })
                ->color('gray'),

            Action::make('saveFinal')
                ->label('Simpan Final')
                ->requiresConfirmation()
                ->modalHeading('Simpan Final Surat Jalan')
                ->modalDescription('Apakah Anda yakin? Setelah disimpan final, Anda tidak dapat mengubah data selain Nama Pengemudi, No. Kendaraan, Ekspedisi, dan Tanggal Pengiriman.')
                ->action(function () {
                    $this->data['status'] = 'RELEASED';
                    $this->create();
                })
                ->color('primary'),

            $this->getCancelFormAction(),
        ];
    }
}
