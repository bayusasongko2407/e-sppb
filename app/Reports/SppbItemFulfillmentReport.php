<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Enums\DeliveryStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SppbItemFulfillmentReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'sppb_item_fulfillment';
    }

    public function getName(): string
    {
        return '4. Laporan Rincian Pemenuhan Barang SPPB';
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
            Select::make('delivery_status')
                ->label('Status Pemenuhan')
                ->placeholder('Semua Status Pemenuhan')
                ->options(collect(DeliveryStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray())
                ->multiple(),
            TextInput::make('search_item')
                ->label('Cari Barang / Barcode / Keterangan')
                ->placeholder('Ketik nama barang / barcode...'),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = SppbDetail::query()->with([
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'item',
            'asset',
            'unit',
        ]);

        if (! $scope->hasModuleAccess($this->getIdentifier())) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        // DocumentAccess Plant & Department Scoping
        if (! empty($scope->allowedPlants)) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('plant_id', $scope->allowedPlants));
        }

        if (! empty($scope->allowedDepartments)) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('department_id', $scope->allowedDepartments));
        }

        // Filters
        if (! empty($filters['start_date'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereDate('request_date', '>=', Carbon::parse($filters['start_date'])));
        }

        if (! empty($filters['end_date'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereDate('request_date', '<=', Carbon::parse($filters['end_date'])));
        }

        if (! empty($filters['delivery_status'])) {
            $query->whereIn('delivery_status', $filters['delivery_status']);
        }

        if (! empty($filters['plant_id'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('plant_id', $filters['plant_id']));
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('department_id', $filters['department_id']));
        }

        if (! empty($filters['search_item'])) {
            $search = $filters['search_item'];
            $query->where(function ($q) use ($search) {
                $q->where('item_asset_name', 'like', "%{$search}%")
                    ->orWhere('reference_code', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('item', fn ($iq) => $iq->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))
                    ->orWhereHas('asset', fn ($aq) => $aq->where('barcode', 'like', "%{$search}%")->orWhere('asset_name', 'like', "%{$search}%"));
            });
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
            TextColumn::make('sppbHeader.plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('sppbHeader.department.name')
                ->label('Departemen')
                ->sortable(),
            TextColumn::make('item_asset_name')
                ->label('Nama Barang / Aset')
                ->searchable()
                ->sortable(),
            TextColumn::make('code_or_barcode')
                ->label('Kode / Barcode')
                ->getStateUsing(fn (SppbDetail $record) => $record->asset?->barcode ?? $record->item?->code ?? $record->reference_code ?? '-'),
            TextColumn::make('quantity')
                ->label('Qty Diminta')
                ->numeric(2)
                ->sortable(),
            TextColumn::make('unit.name')
                ->label('Satuan')
                ->sortable(),
            TextColumn::make('delivery_status')
                ->label('Status Pemenuhan')
                ->badge()
                ->color(function ($state): string {
                    $enum = DeliveryStatus::tryFrom((string) $state);

                    return $enum ? $enum->color() : 'gray';
                })
                ->formatStateUsing(function ($state): string {
                    $enum = DeliveryStatus::tryFrom((string) $state);

                    return $enum ? $enum->label() : (string) $state;
                }),
            TextColumn::make('remarks')
                ->label('Keterangan / Spec')
                ->placeholder('-'),
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
