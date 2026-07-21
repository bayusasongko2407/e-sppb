<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentAccesses\Tables;

use App\Models\DocumentAccess;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DocumentAccessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recipient')
                    ->label('Penerima')
                    ->state(fn (DocumentAccess $record) => $record->role_id ? '[Peran] '.$record->role?->name : '[Pengguna] '.$record->user?->name)
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('role', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(),

                TextColumn::make('plant.name')
                    ->label('Plant')
                    ->state(function (DocumentAccess $record): array {
                        $query = DocumentAccess::query();
                        if ($record->role_id) {
                            $query->where('role_id', $record->role_id);
                        } else {
                            $query->where('user_id', $record->user_id);
                        }

                        return $query->with('plant')
                            ->get()
                            ->pluck('plant.name')
                            ->map(fn ($name) => $name ?? 'Semua Plant')
                            ->unique()
                            ->toArray();
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'Semua Plant' ? 'gray' : 'primary'),

                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->state(function (DocumentAccess $record): array {
                        $query = DocumentAccess::query();
                        if ($record->role_id) {
                            $query->where('role_id', $record->role_id);
                        } else {
                            $query->where('user_id', $record->user_id);
                        }

                        return $query->with('department')
                            ->get()
                            ->pluck('department.name')
                            ->map(fn ($name) => $name ?? 'Semua Departemen')
                            ->unique()
                            ->toArray();
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'Semua Departemen' ? 'gray' : 'success'),

                TextColumn::make('module')
                    ->label('Modul')
                    ->state(function (DocumentAccess $record): array {
                        $query = DocumentAccess::query();
                        if ($record->role_id) {
                            $query->where('role_id', $record->role_id);
                        } else {
                            $query->where('user_id', $record->user_id);
                        }

                        return $query->get()
                            ->pluck('module')
                            ->map(fn ($m) => $m === 'sppb' ? 'SPPB' : 'Pelepasan Barang')
                            ->unique()
                            ->toArray();
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records, BulkAction $action) {
                            foreach ($records as $record) {
                                $query = DocumentAccess::query();
                                if ($record->role_id) {
                                    $query->where('role_id', $record->role_id);
                                } else {
                                    $query->where('user_id', $record->user_id);
                                }
                                $query->delete();
                            }
                            $action->success();
                        }),
                ]),
            ]);
    }
}
