<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailChangeRequests\Tables;

use App\Models\EmailChangeRequest;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class EmailChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_email')
                    ->label('Email Lama')
                    ->searchable(),
                TextColumn::make('new_email')
                    ->label('Email Baru')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PENDING' => 'Menunggu',
                        'APPROVED' => 'Disetujui',
                        'REJECTED' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('requested_at')
                    ->label('Tgl Diajukan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('acted_at')
                    ->label('Tgl Diproses')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'PENDING' => 'Menunggu',
                        'APPROVED' => 'Disetujui',
                        'REJECTED' => 'Ditolak',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Perubahan Email')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui perubahan email ini? Email pengguna akan langsung diperbarui di database.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->visible(fn (EmailChangeRequest $record): bool => $record->status === 'PENDING')
                    ->action(function (EmailChangeRequest $record) {
                        DB::transaction(function () use ($record) {
                            $user = $record->user;
                            $user->email = $record->new_email;
                            $user->save();

                            $record->update([
                                'status' => 'APPROVED',
                                'approved_by_id' => auth()->id(),
                                'acted_at' => now(),
                            ]);
                        });

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Perubahan email telah disetujui. Email pengguna berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->modalHeading('Tolak Perubahan Email')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn (EmailChangeRequest $record): bool => $record->status === 'PENDING')
                    ->action(function (EmailChangeRequest $record, array $data) {
                        $record->update([
                            'status' => 'REJECTED',
                            'approved_by_id' => auth()->id(),
                            'reason' => $data['reason'],
                            'acted_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Ditolak')
                            ->body('Perubahan email telah ditolak.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
