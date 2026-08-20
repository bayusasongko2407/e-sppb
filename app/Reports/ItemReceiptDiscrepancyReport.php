<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Models\Department;
use App\Models\GoodsReleaseItem;
use App\Models\Plant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ItemReceiptDiscrepancyReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'item_receipt_discrepancy';
    }

    public function getName(): string
    {
        return '5. Laporan Selisih & Kondisi Terima Barang';
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
            Select::make('discrepancy_filter')
                ->label('Jenis Selisih / Kondisi')
                ->placeholder('Semua Data Penerimaan')
                ->options([
                    'qty_mismatch' => 'Selisih Qty (Qty Kirim ≠ Qty Terima)',
                    'damaged' => 'Barang Rusak / Bermasalah saat Diterima',
                ])
                ->nullable(),
            TextInput::make('search_item')
                ->label('Cari Barang / Barcode')
                ->placeholder('Ketik nama barang / barcode...'),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = GoodsReleaseItem::query()->with([
            'goodsRelease.sppbHeader.plant',
            'goodsRelease.sppbHeader.department',
            'goodsRelease.receivedBy',
            'sppbDetail.unit',
            'sppbDetail.item',
            'sppbDetail.asset',
        ]);

        if (! $scope->hasModuleAccess($this->getIdentifier())) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        // DocumentAccess Plant & Department Scoping
        if (! empty($scope->allowedPlants)) {
            $query->whereHas('goodsRelease.sppbHeader', fn ($q) => $q->whereIn('plant_id', $scope->allowedPlants));
        }

        if (! empty($scope->allowedDepartments)) {
            $query->whereHas('goodsRelease.sppbHeader', fn ($q) => $q->whereIn('department_id', $scope->allowedDepartments));
        }

        // Filters
        if (! empty($filters['start_date'])) {
            $query->whereHas('goodsRelease', fn ($q) => $q->whereDate('delivery_date', '>=', Carbon::parse($filters['start_date'])));
        }

        if (! empty($filters['end_date'])) {
            $query->whereHas('goodsRelease', fn ($q) => $q->whereDate('delivery_date', '<=', Carbon::parse($filters['end_date'])));
        }

        if (! empty($filters['plant_id'])) {
            $query->whereHas('goodsRelease.sppbHeader', fn ($q) => $q->whereIn('plant_id', $filters['plant_id']));
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('goodsRelease.sppbHeader', fn ($q) => $q->whereIn('department_id', $filters['department_id']));
        }

        if (! empty($filters['discrepancy_filter'])) {
            if ($filters['discrepancy_filter'] === 'qty_mismatch') {
                $query->whereRaw('quantity_released != quantity_received');
            } elseif ($filters['discrepancy_filter'] === 'damaged') {
                $query->whereNotNull('condition_on_receipt')->where('condition_on_receipt', '!=', '');
            }
        }

        if (! empty($filters['search_item'])) {
            $search = $filters['search_item'];
            $query->whereHas('sppbDetail', function ($q) use ($search) {
                $q->where('item_asset_name', 'like', "%{$search}%")
                    ->orWhere('reference_code', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('goodsRelease.release_number')
                ->label('No. Surat Jalan')
                ->sortable()
                ->searchable(),
            TextColumn::make('goodsRelease.sppbHeader.document_number')
                ->label('No. SPPB')
                ->sortable()
                ->searchable(),
            TextColumn::make('goodsRelease.sppbHeader.plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('goodsRelease.sppbHeader.department.name')
                ->label('Departemen')
                ->sortable(),
            TextColumn::make('sppbDetail.item_asset_name')
                ->label('Nama Barang / Aset')
                ->searchable()
                ->sortable(),
            TextColumn::make('quantity_released')
                ->label('Qty Kirim')
                ->numeric(2)
                ->sortable(),
            TextColumn::make('quantity_received')
                ->label('Qty Terima')
                ->numeric(2)
                ->sortable(),
            TextColumn::make('qty_diff')
                ->label('Selisih Qty')
                ->getStateUsing(fn (GoodsReleaseItem $record) => (float) $record->quantity_released - (float) $record->quantity_received)
                ->badge()
                ->color(fn ($state) => (float) $state !== 0.0 ? 'danger' : 'success'),
            TextColumn::make('condition_on_release')
                ->label('Kondisi Saat Kirim')
                ->placeholder('-'),
            TextColumn::make('condition_on_receipt')
                ->label('Kondisi Saat Terima')
                ->placeholder('-')
                ->badge()
                ->color(fn ($state) => ! empty($state) ? 'warning' : 'gray'),
            TextColumn::make('goodsRelease.recipient_name')
                ->label('Penerima')
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
