<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewGoodsRelease extends ViewRecord
{
    protected static string $resource = GoodsReleaseResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            EditAction::make()
                ->visible(fn () => $record && $record->status === 'DRAFT'),

            Action::make('print_pdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('goods-releases.preview', $record))
                ->openUrlInNewTab()
                ->visible(fn () => $record && $record->status !== 'DRAFT'),
        ];
    }
}
