<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsRelease extends CreateRecord
{
    protected static string $resource = GoodsReleaseResource::class;
}
