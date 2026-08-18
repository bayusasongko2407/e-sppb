<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Contracts\WorkflowServiceContract;
use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsRelease extends CreateRecord
{
    protected static string $resource = GoodsReleaseResource::class;

    public string $desiredStatus = 'DRAFT';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $this->desiredStatus;

        if (! empty($data['sppbHeaders']) && is_array($data['sppbHeaders'])) {
            $data['sppb_header_id'] = (int) $data['sppbHeaders'][0];
        } elseif (request()->query('sppb_header_id')) {
            $data['sppb_header_id'] = (int) request()->query('sppb_header_id');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $goodsRelease = $this->record;
        if ($goodsRelease && $goodsRelease->sppbHeader && $goodsRelease->sppbHeader->requester) {
            $header = $goodsRelease->sppbHeader;
            $statusText = $goodsRelease->status === 'RELEASED' ? 'telah diterbitkan (Final)' : 'telah dibuat (Draft)';
            app(WorkflowServiceContract::class)->sendNotification(
                $header->requester,
                'Update Pengeluaran Barang',
                "Surat Jalan #{$goodsRelease->release_number} untuk SPPB {$header->document_number} {$statusText}.",
                SppbHeaderResource::getUrl('view', ['record' => $header]),
                'goods_released',
                [
                    'document_number' => $header->document_number,
                    'requester_name' => $header->requester?->name,
                    'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                    'notes' => "Surat Jalan #{$goodsRelease->release_number} {$statusText}",
                ]
            );
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('saveDraft')
                ->label('Simpan Sebagai Draft')
                ->action(function () {
                    $this->desiredStatus = 'DRAFT';
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
                    $this->desiredStatus = 'RELEASED';
                    $this->data['status'] = 'RELEASED';
                    $this->create();
                })
                ->color('primary'),

            $this->getCancelFormAction(),
        ];
    }
}
