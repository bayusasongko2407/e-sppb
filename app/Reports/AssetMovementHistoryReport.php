<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Plant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class AssetMovementHistoryReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'asset_movement_history';
    }

    public function getName(): string
    {
        return '6. Laporan Tracing & Lokasi Aset Barcode';
    }

    public function getFilterSchema(): array
    {
        return [
            Select::make('plant_id')
                ->label('Plant Aset')
                ->options(Plant::pluck('name', 'id'))
                ->multiple()
                ->live(),
            Select::make('location_id')
                ->label('Lokasi Aset')
                ->options(function (callable $get) {
                    $plantIds = $get('plant_id');
                    $query = Location::query()->with('plant');

                    if (! empty($plantIds)) {
                        $query->whereIn('plant_id', $plantIds);
                    }

                    return $query->get()->mapWithKeys(function ($loc) {
                        $plantCode = $loc->plant ? $loc->plant->code : '-';

                        return [$loc->id => "[{$plantCode}] {$loc->name}"];
                    })->toArray();
                })
                ->multiple(),
            Select::make('condition')
                ->label('Kondisi Fisik')
                ->placeholder('Semua Kondisi')
                ->options([
                    'GOOD' => 'Bagus / Baik',
                    'DAMAGED' => 'Rusak / Perlu Perbaikan',
                ])
                ->multiple(),
            TextInput::make('search_asset')
                ->label('Cari Barcode / Nama Aset')
                ->placeholder('Ketik kode barcode / nama aset...'),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = Asset::query()->with([
            'plant',
            'location',
            'unit',
        ]);

        if (! $scope->hasModuleAccess($this->getIdentifier())) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        // DocumentAccess Plant Scoping
        if (! empty($scope->allowedPlants)) {
            $query->whereIn('plant_id', $scope->allowedPlants);
        }

        // Filters
        if (! empty($filters['plant_id'])) {
            $query->whereIn('plant_id', $filters['plant_id']);
        }

        if (! empty($filters['location_id'])) {
            $query->whereIn('location_id', $filters['location_id']);
        }

        if (! empty($filters['condition'])) {
            $query->whereIn('condition', $filters['condition']);
        }

        if (! empty($filters['search_asset'])) {
            $search = $filters['search_asset'];
            $query->where(function ($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                    ->orWhere('asset_name', 'like', "%{$search}%")
                    ->orWhere('asset_location_data', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('barcode')
                ->label('Kode Barcode')
                ->sortable()
                ->searchable()
                ->copyable(),
            TextColumn::make('asset_name')
                ->label('Nama Aset')
                ->sortable()
                ->searchable(),
            TextColumn::make('plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('location.name')
                ->label('Lokasi Terdaftar')
                ->sortable(),
            TextColumn::make('asset_location_data')
                ->label('Detail Data Lokasi')
                ->placeholder('-'),
            TextColumn::make('condition')
                ->label('Kondisi')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'GOOD' => 'success',
                    'DAMAGED' => 'danger',
                    default => 'warning',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'GOOD' => 'Bagus',
                    'DAMAGED' => 'Rusak',
                    default => $state,
                }),
            TextColumn::make('unit.name')
                ->label('Satuan')
                ->sortable(),
            TextColumn::make('updated_at')
                ->label('Terakhir Diperbarui')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    public function getDefaultSorting(): array
    {
        return [
            'column' => 'id',
            'direction' => 'desc',
        ];
    }
}
