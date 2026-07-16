<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Manager;

use App\Models\SppbHeader;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SppbTrendWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('manager') || $user->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        $query = SppbHeader::query()
            ->select('department_id as id', 'department_id', DB::raw('count(*) as total'))
            ->with('department')
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->limit(10);

        if ($user && ! $user->hasRole('super_admin')) {
            $query->whereExists(function ($rawQuery) use ($user) {
                $rawQuery->select(DB::raw(1))
                    ->from('document_accesses')
                    ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                    ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                    ->where('document_accesses.user_id', $user->id)
                    ->where('document_accesses.module', 'sppb')
                    ->where('document_accesses.can_view', true);
            });
        }

        return $table
            ->query($query)
            ->heading('Top Departemen — SPPB Bulan Ini')
            ->columns([
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total SPPB')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trend')
                    ->label('Persentase')
                    ->state(function (SppbHeader $record) use ($user) {
                        $totalQuery = SppbHeader::whereMonth('created_at', Carbon::now()->month);

                        if ($user && ! $user->hasRole('super_admin')) {
                            $totalQuery->whereExists(function ($rawQuery) use ($user) {
                                $rawQuery->select(DB::raw(1))
                                    ->from('document_accesses')
                                    ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                                    ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                                    ->where('document_accesses.user_id', $user->id)
                                    ->where('document_accesses.module', 'sppb')
                                    ->where('document_accesses.can_view', true);
                            });
                        }

                        $totalMonth = $totalQuery->count();

                        return $totalMonth > 0 ? round(($record->total / $totalMonth) * 100, 1).'%' : '0%';
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->paginated(false);
    }
}
