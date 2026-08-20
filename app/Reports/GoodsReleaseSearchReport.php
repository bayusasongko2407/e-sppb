<?php

declare(strict_types=1);

namespace App\Reports;

use App\Contracts\Reporting\ReportInterface;
use App\DTOs\Reporting\ReportScope;
use App\Enums\GoodsReleaseStatus;
use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Plant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GoodsReleaseSearchReport implements ReportInterface
{
    public function getIdentifier(): string
    {
        return 'goods_release_search';
    }

    public function getName(): string
    {
        return '2. Laporan Surat Jalan & Status Pengiriman';
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
                ->label('Status Surat Jalan')
                ->placeholder('Semua Status')
                ->options(collect(GoodsReleaseStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray())
                ->multiple(),
        ];
    }

    public function getQuery(ReportScope $scope, array $filters): Builder
    {
        $query = GoodsRelease::query()->with([
            'sppbHeader.plant',
            'sppbHeader.department',
            'createdBy',
            'senderUser',
            'receiverUser',
            'receivedBy',
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
            $query->whereDate('delivery_date', '>=', Carbon::parse($filters['start_date']));
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('delivery_date', '<=', Carbon::parse($filters['end_date']));
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (! empty($filters['plant_id'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('plant_id', $filters['plant_id']));
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('sppbHeader', fn ($q) => $q->whereIn('department_id', $filters['department_id']));
        }

        return $query;
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('release_number')
                ->label('No. Surat Jalan')
                ->sortable()
                ->searchable(),
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
            TextColumn::make('delivery_date')
                ->label('Tanggal Kirim')
                ->date('d/m/Y')
                ->sortable(),
            TextColumn::make('driver_name')
                ->label('Pengemudi')
                ->searchable(),
            TextColumn::make('vehicle_number')
                ->label('No. Polisi')
                ->searchable(),
            TextColumn::make('expedition_name')
                ->label('Ekspedisi')
                ->searchable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(function ($state): string {
                    $enum = GoodsReleaseStatus::tryFrom((string) $state);

                    return $enum ? $enum->color() : 'gray';
                })
                ->formatStateUsing(function ($state): string {
                    $enum = GoodsReleaseStatus::tryFrom((string) $state);

                    return $enum ? $enum->label() : (string) $state;
                }),
            TextColumn::make('recipient_name')
                ->label('Penerima')
                ->getStateUsing(fn (GoodsRelease $record) => $record->recipient_name ?? $record->receivedBy?->name ?? '-'),
            TextColumn::make('received_at')
                ->label('Tanggal Diterima')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    public function getDefaultSorting(): array
    {
        return [
            'column' => 'delivery_date',
            'direction' => 'desc',
        ];
    }
}
