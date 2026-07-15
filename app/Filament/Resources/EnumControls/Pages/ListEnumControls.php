<?php

namespace App\Filament\Resources\EnumControls\Pages;

use App\Filament\Resources\EnumControls\EnumControlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnumControls extends ListRecords
{
    protected static string $resource = EnumControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
