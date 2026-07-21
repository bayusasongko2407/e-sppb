<?php

declare(strict_types=1);

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Exports\DepartmentExporter;
use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->label('Export Data')
                ->exporter(DepartmentExporter::class),
        ];
    }
}
