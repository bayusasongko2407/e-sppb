<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plant.name')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('asset_name')
                    ->label('Nama Aset')
                    ->searchable(),
                TextColumn::make('asset_location_data')
                    ->label('Data Lokasi Aset')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->searchable(),
                TextColumn::make('unit.name')
                    ->label('Satuan')
                    ->searchable(),
                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'GOOD' => 'Baik',
                        'FAIR' => 'Cukup',
                        'POOR', 'DAMAGED' => 'Rusak',
                        default => $state ?? 'Baik',
                    })
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'GOOD' => 'success',
                        'FAIR' => 'warning',
                        'POOR', 'DAMAGED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'AVAILABLE' => 'Tersedia',
                        'IN_USE', 'USED' => 'Digunakan',
                        'MAINTENANCE' => 'Perbaikan',
                        'DISPOSED', 'SCRAPPED' => 'Dihapuskan',
                        default => $state ?? 'Tersedia',
                    })
                    ->color(fn (?string $state): string => match (strtoupper((string) $state)) {
                        'AVAILABLE' => 'success',
                        'IN_USE', 'USED' => 'info',
                        'MAINTENANCE' => 'warning',
                        'DISPOSED', 'SCRAPPED' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
