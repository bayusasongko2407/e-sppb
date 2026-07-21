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
use App\Models\SppbHeader;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SppbReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'sppb';
    }

    public function getName(): string
    {
        return 'Laporan SPPB (Berdasarkan Dokumen)';
    }

    public function getFilterSchema(): array
    {
        return [
            TextInput::make('document_number')
                ->label('No. SPPB'),
            DatePicker::make('start_date')
                ->label('Tanggal Awal'),
            DatePicker::make('end_date')
                ->label('Tanggal Akhir'),
            Select::make('status')
                ->label('Status')
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
            Select::make('requester_id')
                ->label('Requester')
                ->options(User::pluck('name', 'id'))
                ->searchable()
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
        $query = SppbHeader::query()->with(['plant', 'department', 'requester', 'originLocation', 'destinationLocation', 'details']);

        // Apply ReportScope DocumentAccess Constraints
        if (! $scope->hasModuleAccess($this->getIdentifier())) {
            // Force no results if they don't have module access
            $query->whereRaw('1 = 0');

            return $query;
        }

        if (! empty($scope->allowedPlants)) {
            $query->whereIn('plant_id', $scope->allowedPlants);
        }

        if (! empty($scope->allowedDepartments)) {
            $query->whereIn('department_id', $scope->allowedDepartments);
        }

        // Apply User Filters
        if (! empty($filters['start_date'])) {
            $query->whereDate('request_date', '>=', Carbon::parse($filters['start_date']));
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('request_date', '<=', Carbon::parse($filters['end_date']));
        }

        if (! empty($filters['document_number'])) {
            $query->where('document_number', 'like', '%'.$filters['document_number'].'%');
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (! empty($filters['plant_id'])) {
            $query->whereIn('plant_id', $filters['plant_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereIn('department_id', $filters['department_id']);
        }

        if (! empty($filters['requester_id'])) {
            $query->whereIn('requester_id', $filters['requester_id']);
        }

        if (! empty($filters['item_id'])) {
            $query->whereHas('details', function ($q) use ($filters) {
                $q->whereIn('item_id', $filters['item_id']);
            });
        }

        if (! empty($filters['asset_id'])) {
            $query->whereHas('details', function ($q) use ($filters) {
                $q->whereIn('asset_id', $filters['asset_id']);
            });
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('document_number')
                ->label('No. SPPB')
                ->sortable()
                ->searchable(),
            TextColumn::make('request_date')
                ->label('Tanggal')
                ->date()
                ->sortable(),
            TextColumn::make('plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('department.name')
                ->label('Department')
                ->sortable(),
            TextColumn::make('requester.name')
                ->label('Requester')
                ->sortable(),
            TextColumn::make('originLocation.name')
                ->label('Lokasi Asal')
                ->sortable(),
            TextColumn::make('destinationLocation.name')
                ->label('Lokasi Tujuan')
                ->sortable(),
            TextColumn::make('details.item_asset_name')
                ->label('Item/Aset')
                ->listWithLineBreaks()
                ->bulleted(),
            TextColumn::make('status')
                ->label('Status')
                ->badge(),
        ];
    }

    public function getDefaultSorting(): array
    {
        return [
            'column' => 'request_date',
            'direction' => 'desc',
        ];
    }
}
