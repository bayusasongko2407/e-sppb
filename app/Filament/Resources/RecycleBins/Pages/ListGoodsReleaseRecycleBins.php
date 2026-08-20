<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Pages;

use App\Filament\Resources\RecycleBins\GoodsReleaseRecycleBinResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListGoodsReleaseRecycleBins extends ListRecords
{
    protected static string $resource = GoodsReleaseRecycleBinResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
