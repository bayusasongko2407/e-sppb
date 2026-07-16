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
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('plant.name')
                    ->label('Plant')
                    ->state(function (DocumentAccess $record): string {
                        if (! $record->relationLoaded('user') || ! $record->user->relationLoaded('documentAccesses')) {
                            return $record->plant?->name ?? '';
                        }

                        return $record->user->documentAccesses
                            ->pluck('plant.name')
                            ->filter()
                            ->unique()
                            ->implode(', ');
                    }),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->state(function (DocumentAccess $record): string {
                        if (! $record->relationLoaded('user') || ! $record->user->relationLoaded('documentAccesses')) {
                            return $record->department?->name ?? '';
                        }

                        return $record->user->documentAccesses
                            ->pluck('department.name')
                            ->filter()
                            ->unique()
                            ->implode(', ');
                    }),
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
                            $userIds = $records->pluck('user_id')->unique()->toArray();
                            DocumentAccess::whereIn('user_id', $userIds)->delete();
                            $action->success();
                        }),
                ]),
            ]);
    }
}
