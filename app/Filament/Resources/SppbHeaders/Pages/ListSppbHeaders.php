<?php

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSppbHeaders extends ListRecords
{
    protected static string $resource = SppbHeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
