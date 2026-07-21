<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Tables;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SppbHeadersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('No. SPPB')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('request_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('plant.name')
                    ->label('Plant')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('requester.name')
                    ->label('Pemohon')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('needed_name')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof SppbStatus
                        ? $state->label()
                        : (SppbStatus::tryFrom($state)?->label() ?? $state))
                    ->color(fn ($state): string => $state instanceof SppbStatus
                        ? $state->color()
                        : (SppbStatus::tryFrom($state)?->color() ?? 'gray'))
                    ->icon(fn ($state): string => $state instanceof SppbStatus
                        ? $state->icon()
                        : (SppbStatus::tryFrom($state)?->icon() ?? 'heroicon-o-question-mark-circle'))
                    ->sortable(),

                TextColumn::make('date_needed')
                    ->label('Tgl. Kebutuhan')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('Belum Ada SPPB')
            ->emptyStateDescription('Mulai buat SPPB baru dengan klik tombol di atas.')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(SppbStatus::cases())
                            ->mapWithKeys(fn (SppbStatus $s) => [$s->value => $s->label()])
                            ->toArray()
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->color('info')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (SppbHeader $record) => route('sppb.preview', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn (SppbHeader $record): bool => in_array($record->status, [
                        SppbStatus::APPROVED->value,
                        SppbStatus::RELEASE_IN_PROGRESS->value,
                        SppbStatus::COMPLETED->value,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
