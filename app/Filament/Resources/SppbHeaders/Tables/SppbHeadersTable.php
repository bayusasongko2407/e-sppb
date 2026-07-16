<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Tables;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                Action::make('submit_approval')
                    ->label('Ajukan Persetujuan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan SPPB')
                    ->modalDescription('Apakah Anda yakin ingin mengajukan SPPB ini untuk proses persetujuan?')
                    ->modalSubmitActionLabel('Ya, Ajukan')
                    ->visible(fn (SppbHeader $record): bool => in_array($record->status, [SppbStatus::DRAFT->value, SppbStatus::REJECTED->value]))
                    ->action(function (SppbHeader $record, WorkflowServiceContract $workflowService) {
                        try {
                            $workflowService->queueSubmission(new SubmitSppbData(
                                sppbHeaderId: $record->id,
                                actorId: auth()->id(),
                                commandUuid: Str::uuid()->toString(),
                            ));

                            Notification::make()
                                ->title('Berhasil')
                                ->body('SPPB berhasil masuk antrean pengajuan.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Terjadi kesalahan: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),

                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->color('info')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (SppbHeader $record) => route('sppb.preview', ['id' => $record->id]))
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
