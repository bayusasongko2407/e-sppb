<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Tables;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecycleBinsTable
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
                    ->label('Tanggal Permintaan')
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

                TextColumn::make('related_goods_releases')
                    ->label('Surat Jalan Terkait')
                    ->state(function (SppbHeader $record): string {
                        $releases = $record->goodsReleases()->withTrashed()->get();
                        if ($releases->isEmpty()) {
                            return 'Belum Ada';
                        }

                        return $releases->map(function ($r) {
                            $tag = $r->trashed() ? '[Recycle Bin]' : '[Aktif]';
                            $num = $r->is_manual ? ($r->manual_release_number ?? $r->release_number) : $r->release_number;

                            return "{$num} {$tag}";
                        })->implode(', ');
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status Terakhir')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof SppbStatus
                        ? $state->label()
                        : (SppbStatus::tryFrom($state)?->label() ?? (string) $state))
                    ->color(fn ($state): string => $state instanceof SppbStatus
                        ? $state->color()
                        : (SppbStatus::tryFrom($state)?->color() ?? 'gray'))
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('Tanggal Dihapus')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('deleted_at', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-trash')
            ->emptyStateHeading('Recycle Bin Kosong')
            ->emptyStateDescription('Tidak ada dokumen SPPB terhapus saat ini.')
            ->recordActions([
                ViewAction::make(),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Dokumen SPPB')
                    ->modalDescription('Apakah Anda yakin ingin memulihkan dokumen SPPB ini kembali ke daftar utama?'),
                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Dokumen SPPB')
                    ->modalDescription('Apakah Anda yakin ingin menghapus permanen dokumen ini? Data yang dihapus permanen tidak dapat dikembalikan.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                    ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih'),
                ]),
            ]);
    }
}
