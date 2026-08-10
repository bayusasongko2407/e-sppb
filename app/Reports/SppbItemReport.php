<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\EnumControl;
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
            Select::make('status')
                ->label('Status SPPB')
                ->placeholder('Semua Status')
                ->helperText('Jika kosong, menampilkan semua status')
                ->options(function () {
                    $options = EnumControl::where('table_name', 'sppb_headers')
                        ->where('column_name', 'status')
                        ->where('is_active', true)
                        ->orderBy('sequence')
                        ->pluck('label', 'value')
                        ->toArray();

                    if (empty($options)) {
                        $options = collect(SppbStatus::cases())
                            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                            ->toArray();
                    }

                    return $options;
                })
                ->multiple(),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = SppbDetail::query()->with([
            'sppbHeader.plant',
            'sppbHeader.department',
            'sppbHeader.requester',
            'sppbHeader.originLocation',
            'sppbHeader.destinationLocation',
            'sppbHeader.goodsReleases.createdBy',
            'sppbHeader.goodsReleases.senderUser',
            'unit',
            'item',
            'asset',
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
            TextColumn::make('sppbHeader.plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('sppbHeader.department.name')
                ->label('Departement')
                ->sortable(),
            TextColumn::make('sppbHeader.requester.name')
                ->label('Pemohon')
                ->sortable(),
            TextColumn::make('sppbHeader.document_number')
                ->label('No. SPPB')
                ->sortable()
                ->searchable(),
            TextColumn::make('sppbHeader.request_date')
                ->label('Tanggal SPPB')
                ->date('d/m/Y')
                ->sortable(),
            TextColumn::make('sppbHeader.status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(function ($state): string {
                    $val = $state instanceof SppbStatus ? $state->value : (string) $state;

                    $enumLabel = EnumControl::where('table_name', 'sppb_headers')
                        ->where('column_name', 'status')
                        ->where('value', $val)
                        ->where('is_active', true)
                        ->value('label');

                    if ($enumLabel) {
                        return $enumLabel;
                    }

                    return $state instanceof SppbStatus
                        ? $state->label()
                        : (SppbStatus::tryFrom($val)?->label() ?? $val);
                })
                ->color(function ($state): string {
                    $val = $state instanceof SppbStatus ? $state->value : (string) $state;

                    return $state instanceof SppbStatus
                        ? $state->color()
                        : (SppbStatus::tryFrom($val)?->color() ?? 'gray');
                })
                ->icon(function ($state): string {
                    $val = $state instanceof SppbStatus ? $state->value : (string) $state;

                    return $state instanceof SppbStatus
                        ? $state->icon()
                        : (SppbStatus::tryFrom($val)?->icon() ?? 'heroicon-o-question-mark-circle');
                }),
            TextColumn::make('sppbHeader.date_needed')
                ->label('Tanggal Permintaan')
                ->date('d/m/Y')
                ->sortable(),
            TextColumn::make('sppbHeader.needed_name')
                ->label('Keperluan'),
            TextColumn::make('sppbHeader.originLocation.name')
                ->label('Lokasi Asal'),
            TextColumn::make('sppbHeader.destinationLocation.name')
                ->label('Lokasi Tujuan'),
            TextColumn::make('item_type')
                ->label('Jenis Barang')
                ->getStateUsing(fn (SppbDetail $record) => $record->asset_id ? 'Asset' : ($record->item_id ? 'Item Master' : 'Non-Asset/Manual')),
            TextColumn::make('reference_code')
                ->label('No. Barcode')
                ->getStateUsing(fn (SppbDetail $record) => $record->reference_code ?? $record->asset?->asset_code ?? $record->item?->code ?? '-'),
            TextColumn::make('item_asset_name')
                ->label('Nama Aset/Barang')
                ->sortable()
                ->searchable(),
            TextColumn::make('quantity')
                ->label('QTY')
                ->numeric(),
            TextColumn::make('unit.name')
                ->label('Satuan')
                ->sortable(),
            TextColumn::make('remarks')
                ->label('Spesifikasi'),
            TextColumn::make('release_date')
                ->label('Tanggal Pengiriman')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->delivery_date ? Carbon::parse($record->sppbHeader->goodsReleases->last()->delivery_date)->format('d/m/Y') : '-'),
            TextColumn::make('delivery_status')
                ->label('Status Pengiriman Barang')
                ->getStateUsing(fn (SppbDetail $record) => $record->delivery_status_label),
            TextColumn::make('sender_name')
                ->label('Nama Pengirim')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->createdBy?->name ?? $record->sppbHeader?->goodsReleases->last()?->senderUser?->name ?? $record->sppbHeader?->goodsReleases->last()?->sender_name ?? '-'),
            TextColumn::make('driver_name')
                ->label('Nama Pengemudi')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->driver_name ?? '-'),
            TextColumn::make('vehicle_number')
                ->label('No Kendaraan')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->vehicle_number ?? '-'),
            TextColumn::make('expedition')
                ->label('Ekspedisi')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->expedition_name ?? '-'),
            TextColumn::make('shipping_remarks')
                ->label('Keterangan Pengiriman')
                ->getStateUsing(fn (SppbDetail $record) => $record->sppbHeader?->goodsReleases->last()?->notes ?? '-'),
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
