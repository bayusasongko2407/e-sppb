<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGoodsRelease extends EditRecord
{
    protected static string $resource = GoodsReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
