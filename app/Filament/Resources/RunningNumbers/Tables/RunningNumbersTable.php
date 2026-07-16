<?php

namespace App\Filament\Resources\RunningNumbers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RunningNumbersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->label('Tipe Dokumen')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('plant.name')
                    ->label('Pabrik')
                    ->searchable(),
                TextColumn::make('period_key')
                    ->label('Periode')
                    ->searchable(),
                TextColumn::make('prefix')
                    ->label('Prefix')
                    ->searchable(),
                TextColumn::make('digits')
                    ->label('Digit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_number')
                    ->label('Nomor Terakhir')
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
