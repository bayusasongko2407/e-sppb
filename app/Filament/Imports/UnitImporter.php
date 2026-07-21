<?php

namespace App\Filament\Imports;

use App\Models\Unit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class UnitImporter extends Importer
{
    protected static ?string $model = Unit::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
            ImportColumn::make('category')
                ->rules(['max:50']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): Unit
    {
        return Unit::firstOrNew([
            'code' => $this->data['code'] ?? '',
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your unit import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
