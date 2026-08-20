<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Models\DocumentValidation;
use App\Models\Plant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DocumentValidationLogReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'document_validation_log';
    }

    public function getName(): string
    {
        return '3. Laporan Keaslian & Scan QR Dokumen';
    }

    public function getFilterSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Tanggal Awal *')
                ->required()
                ->default(now()->startOfMonth()->format('Y-m-d')),
            DatePicker::make('end_date')
                ->label('Tanggal Akhir *')
                ->required()
                ->default(now()->endOfMonth()->format('Y-m-d')),
            Select::make('plant_id')
                ->label('Plant')
                ->options(Plant::pluck('name', 'id'))
                ->multiple(),
            Select::make('validation_result')
                ->label('Hasil Verifikasi')
                ->placeholder('Semua Hasil')
                ->options([
                    'VALID' => 'VALID (Resmi)',
                    'INVALID' => 'INVALID (Tidak Sah)',
                ])
                ->multiple(),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = DocumentValidation::query()->with([
            'documentGeneration.plant',
            'actor',
        ]);

        if (! $scope->hasModuleAccess($this->getIdentifier())) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        // DocumentAccess Plant Scoping
        if (! empty($scope->allowedPlants)) {
            $query->whereHas('documentGeneration', fn ($q) => $q->whereIn('plant_id', $scope->allowedPlants));
        }

        // Filters
        if (! empty($filters['start_date'])) {
            $query->whereDate('verified_at', '>=', Carbon::parse($filters['start_date']));
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('verified_at', '<=', Carbon::parse($filters['end_date']));
        }

        if (! empty($filters['validation_result'])) {
            $query->whereIn('validation_result', $filters['validation_result']);
        }

        if (! empty($filters['plant_id'])) {
            $query->whereHas('documentGeneration', fn ($q) => $q->whereIn('plant_id', $filters['plant_id']));
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('verified_at')
                ->label('Waktu Verifikasi')
                ->dateTime('d/m/Y H:i:s')
                ->sortable(),
            TextColumn::make('documentGeneration.document_number')
                ->label('No. Dokumen')
                ->sortable()
                ->searchable(),
            TextColumn::make('documentGeneration.document_type')
                ->label('Jenis Dokumen')
                ->sortable(),
            TextColumn::make('documentGeneration.plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('verification_channel')
                ->label('Kanal Verifikasi')
                ->badge(),
            TextColumn::make('validation_result')
                ->label('Hasil Verifikasi')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'VALID' => 'success',
                    'INVALID' => 'danger',
                    default => 'warning',
                }),
            TextColumn::make('actor.name')
                ->label('Diverifikasi Oleh')
                ->placeholder('Public / Guest Scan'),
            TextColumn::make('lookup_fingerprint_sha256')
                ->label('Fingerprint Hash')
                ->limit(20)
                ->copyable(),
        ];
    }

    public function getDefaultSorting(): array
    {
        return [
            'column' => 'verified_at',
            'direction' => 'desc',
        ];
    }
}
