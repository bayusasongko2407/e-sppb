<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Enums\SppbStatus;
use App\Models\Asset;
use App\Models\Department;
use App\Models\Item;
use App\Models\Plant;
use App\Models\SppbDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SppbItemReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'sppb_items';
    }

    public function getName(): string
    {
        return 'Laporan SPPB (Berdasarkan Item & Aset)';
    }

    public function getFilterSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Tanggal Awal (SPPB)'),
            DatePicker::make('end_date')
                ->label('Tanggal Akhir (SPPB)'),
            Select::make('status')
                ->label('Status SPPB')
                ->options(
                    collect(SppbStatus::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                        ->toArray()
                )
                ->multiple(),
            Select::make('plant_id')
                ->label('Plant')
                ->options(Plant::pluck('name', 'id'))
                ->multiple()
                ->live(),
            Select::make('department_id')
                ->label('Department')
                ->options(function (callable $get) {
                    $plantIds = $get('plant_id');
                    $query = Department::query()->with('plant');

                    if (! empty($plantIds)) {
                        $query->whereIn('plant_id', $plantIds);
                    }

                    return $query->get()->mapWithKeys(function ($dept) {
                        $plantCode = $dept->plant ? $dept->plant->code : '-';

                        return [$dept->id => "[{$plantCode}] {$dept->name}"];
                    })->toArray();
                })
                ->multiple(),
            Select::make('item_id')
                ->label('Item')
                ->options(Item::pluck('name', 'id'))
                ->searchable()
                ->multiple(),
            Select::make('asset_id')
                ->label('Asset')
                ->options(Asset::pluck('asset_name', 'id'))
                ->searchable()
                ->multiple(),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = SppbDetail::query()->with([
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'unit',
        ]);

        // Access checking using the sppb module permission
        if (! $scope->hasModuleAccess('sppb')) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->whereHas('sppbHeader', function ($q) use ($scope, $filters) {
            if (! empty($scope->allowedPlants)) {
                $q->whereIn('plant_id', $scope->allowedPlants);
            }

            if (! empty($scope->allowedDepartments)) {
                $q->whereIn('department_id', $scope->allowedDepartments);
            }

            if (! empty($filters['start_date'])) {
                $q->whereDate('request_date', '>=', Carbon::parse($filters['start_date']));
            }

            if (! empty($filters['end_date'])) {
                $q->whereDate('request_date', '<=', Carbon::parse($filters['end_date']));
            }

            if (! empty($filters['status'])) {
                $q->whereIn('status', $filters['status']);
            }

            if (! empty($filters['plant_id'])) {
                $q->whereIn('plant_id', $filters['plant_id']);
            }

            if (! empty($filters['department_id'])) {
                $q->whereIn('department_id', $filters['department_id']);
            }
        });

        // Detail level filters
        if (! empty($filters['item_id'])) {
            $query->whereIn('item_id', $filters['item_id']);
        }

        if (! empty($filters['asset_id'])) {
            $query->whereIn('asset_id', $filters['asset_id']);
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('sppbHeader.document_number')
                ->label('No. SPPB')
                ->sortable()
                ->searchable(),
            TextColumn::make('sppbHeader.request_date')
                ->label('Tanggal SPPB')
                ->date()
                ->sortable(),
            TextColumn::make('sppbHeader.plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('item_asset_name')
                ->label('Nama Item/Aset')
                ->sortable()
                ->searchable(),
            TextColumn::make('quantity')
                ->label('Qty')
                ->numeric(),
            TextColumn::make('unit.name')
                ->label('Satuan')
                ->sortable(),
            TextColumn::make('sppbHeader.status')
                ->label('Status SPPB')
                ->badge(),
        ];
    }

    public function getDefaultSorting(): array
    {
        return [
            'column' => 'id', // Default sorting
            'direction' => 'desc',
        ];
    }
}
