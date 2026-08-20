<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins\Tables;

use App\Enums\GoodsReleaseStatus;
use App\Models\GoodsRelease;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoodsReleaseRecycleBinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('release_number')
                    ->label('No. Surat Jalan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('sppbHeader.document_number')
                    ->label('SPPB Terkait')
                    ->searchable()
                    ->sortable()
                    ->state(function (GoodsRelease $record): string {
                        $sppb = $record->sppbHeader()->withTrashed()->first();
                        if (! $sppb) {
                            return '—';
                        }
                        $tag = $sppb->trashed() ? '[Recycle Bin]' : '[Aktif]';

                        return "{$sppb->document_number} {$tag}";
                    })
                    ->badge()
                    ->color(function (GoodsRelease $record): string {
                        $sppb = $record->sppbHeader()->withTrashed()->first();

                        return $sppb?->trashed() ? 'warning' : 'success';
                    }),

                TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('receiver_name')
                    ->label('Penerima')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('driver_name')
                    ->label('Pengemudi')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('vehicle_number')
                    ->label('No. Polisi')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status Terakhir')
                    ->badge()
                    ->color(function (string $state): string {
                        $enum = GoodsReleaseStatus::tryFrom($state);

                        return $enum ? $enum->color() : 'gray';
                    })
                    ->formatStateUsing(function (string $state): string {
                        $enum = GoodsReleaseStatus::tryFrom($state);

                        return $enum ? $enum->label() : $state;
                    })
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label('Tanggal Dihapus')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('deleted_at', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-trash')
            ->emptyStateHeading('Recycle Bin Surat Jalan Kosong')
            ->emptyStateDescription('Tidak ada dokumen Surat Jalan terhapus saat ini.')
            ->recordActions([
                ViewAction::make(),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan Surat Jalan')
                    ->modalDescription('Apakah Anda yakin ingin memulihkan dokumen Surat Jalan ini kembali ke daftar utama?')
                    ->after(function (GoodsRelease $record) {
                        $sppb = $record->sppbHeader()->withTrashed()->first();
                        if ($sppb && $sppb->trashed()) {
                            $sppb->restore();
                        }
                    }),
                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Surat Jalan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus permanen dokumen Surat Jalan ini? Data yang dihapus permanen tidak dapat dikembalikan.'),
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
