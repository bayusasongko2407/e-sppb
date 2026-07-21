<?php

namespace App\Filament\Imports;

use App\Models\Location;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LocationImporter extends Importer
{
    protected static ?string $model = Location::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('plant')
                ->requiredMapping()
                ->relationship('code')
                ->rules(['required']),
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:150']),
            ImportColumn::make('address'),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): Location
    {
        return Location::firstOrNew([
            'plant_id' => $this->record->plant_id ?? null,
            'code' => $this->data['code'] ?? '',
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your location import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
