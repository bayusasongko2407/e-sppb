<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Exports\UnitExporter;
use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->label('Export Data')
                ->exporter(UnitExporter::class),
        ];
    }
}
