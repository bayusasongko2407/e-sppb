<?php

namespace App\Filament\Imports;

use App\Models\Department;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DepartmentImporter extends Importer
{
    protected static ?string $model = Department::class;

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
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): Department
    {
        return Department::firstOrNew([
            'plant_id' => $this->record->plant_id ?? null,
            'code' => $this->data['code'] ?? '',
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your department import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
