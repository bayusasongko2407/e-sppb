<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGoodsReleases extends ListRecords
{
    protected static string $resource = GoodsReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
