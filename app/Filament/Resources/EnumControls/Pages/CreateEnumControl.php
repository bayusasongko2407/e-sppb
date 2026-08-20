<?php

namespace App\Filament\Resources\EnumControls\Pages;

use App\Filament\Resources\EnumControls\EnumControlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnumControl extends CreateRecord
{
    protected static string $resource = EnumControlResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
