<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Pages;

use App\Filament\Resources\RecycleBins\RecycleBinResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListRecycleBins extends ListRecords
{
    protected static string $resource = RecycleBinResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
