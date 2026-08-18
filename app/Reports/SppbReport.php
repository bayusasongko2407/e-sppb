<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\EnumControl;
use App\Models\Plant;
use App\Models\SppbHeader;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                ->label('Status')
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
        $query = SppbHeader::query()->with([
            'plant',
            'department',
            'requester',
            'originLocation',
            'destinationLocation',
            'details',
            'goodsReleases.createdBy',
            'goodsReleases.senderUser',
        ]);

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

        if (! empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (! empty($filters['plant_id'])) {
            $query->whereIn('plant_id', $filters['plant_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereIn('department_id', $filters['department_id']);
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('plant.name')
                ->label('Plant')
                ->sortable(),
            TextColumn::make('department.name')
                ->label('Departement')
                ->sortable(),
            TextColumn::make('requester.name')
                ->label('Pemohon')
                ->sortable(),
            TextColumn::make('document_number')
                ->label('No. SPPB')
                ->sortable()
                ->searchable(),
            TextColumn::make('request_date')
                ->label('Tanggal SPPB')
                ->date('d/m/Y')
                ->sortable(),
            TextColumn::make('status')
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
            TextColumn::make('date_needed')
                ->label('Tanggal Permintaan')
                ->date('d/m/Y')
                ->sortable(),
            TextColumn::make('needed_name')
                ->label('Keperluan'),
            TextColumn::make('originLocation.name')
                ->label('Lokasi Asal')
                ->sortable(),
            TextColumn::make('destinationLocation.name')
                ->label('Lokasi Tujuan')
                ->sortable(),
            TextColumn::make('release_date')
                ->label('Tanggal Pengiriman')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->delivery_date ? Carbon::parse($record->goodsReleases->last()->delivery_date)->format('d/m/Y') : '-'),
            TextColumn::make('release_status')
                ->label('Status Pengiriman')
                ->getStateUsing(function (SppbHeader $record): string {
                    $st = $record->goodsReleases->last()?->status;
                    if (! $st) {
                        return '-';
                    }

                    return match (strtoupper((string) $st)) {
                        'DRAFT' => 'Draft',
                        'RELEASED', 'IN_TRANSIT', 'PENDING' => 'Dalam Pengiriman',
                        'DELIVERED', 'RECEIVED', 'COMPLETED' => 'Terkirim',
                        'CANCELLED' => 'Dibatalkan',
                        default => (string) $st,
                    };
                }),
            TextColumn::make('sender_name')
                ->label('Nama Pengirim')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->createdBy?->name ?? $record->goodsReleases->last()?->senderUser?->name ?? $record->goodsReleases->last()?->sender_name ?? '-'),
            TextColumn::make('driver_name')
                ->label('Nama Pengemudi')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->driver_name ?? '-'),
            TextColumn::make('vehicle_number')
                ->label('No Kendaraan')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->vehicle_number ?? '-'),
            TextColumn::make('expedition')
                ->label('Ekspedisi')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->expedition_name ?? '-'),
            TextColumn::make('shipping_remarks')
                ->label('Keterangan Pengiriman')
                ->getStateUsing(fn (SppbHeader $record) => $record->goodsReleases->last()?->notes ?? '-'),
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
