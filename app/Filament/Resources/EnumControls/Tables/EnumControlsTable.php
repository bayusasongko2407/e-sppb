<?php

namespace App\Filament\Resources\EnumControls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnumControlsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('table_name')
                    ->label('Tabel Database')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('column_name')
                    ->label('Kolom Database')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Nilai Internal')
                    ->searchable(),
                TextColumn::make('label')
                    ->label('Teks Tampilan')
                    ->searchable(),
                TextColumn::make('sequence')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
