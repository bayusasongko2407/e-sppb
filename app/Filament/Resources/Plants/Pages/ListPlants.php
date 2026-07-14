<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plants\Pages;

use App\Filament\Resources\Plants\PlantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlants extends ListRecords
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
