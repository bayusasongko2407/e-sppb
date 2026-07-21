<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plants\Pages;

use App\Filament\Exports\PlantExporter;
use App\Filament\Resources\Plants\PlantResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListPlants extends ListRecords
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->label('Export Data')
                ->exporter(PlantExporter::class),
        ];
    }
}
