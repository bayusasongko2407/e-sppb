<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('plant')
                ->relationship('code'),
            ImportColumn::make('location')
                ->relationship('code'),
            ImportColumn::make('asset_name')
                ->rules(['max:255']),
            ImportColumn::make('asset_location_data')
                ->rules(['max:255']),
            ImportColumn::make('barcode')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
            ImportColumn::make('condition')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('unit')
                ->requiredMapping()
                ->relationship('code')
                ->rules(['required']),
            ImportColumn::make('notes'),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): Asset
    {
        return Asset::firstOrNew([
            'barcode' => $this->data['barcode'] ?? '',
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
