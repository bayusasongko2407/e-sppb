<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Schemas;

use App\Enums\SppbStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SppbHeaderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── SECTION 1: INFORMASI HEADER ──────────────────────────────
                Section::make('Informasi Header')
                    ->schema([
                        // ROW 1: No. SPPB | Tgl Permintaan | Status | Plant | Department | Requester
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 6,
                        ])->schema([
                            TextEntry::make('document_number')
                                ->label('No. SPPB')
                                ->placeholder('—'),

                            TextEntry::make('request_date')
                                ->label('Tanggal Permintaan')
                                ->date('d/m/Y')
                                ->placeholder('—'),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn ($state): string => $state instanceof SppbStatus
                                    ? $state->label()
                                    : (SppbStatus::tryFrom($state)?->label() ?? $state))
                                ->color(fn ($state): string => $state instanceof SppbStatus
                                    ? $state->color()
                                    : (SppbStatus::tryFrom($state)?->color() ?? 'gray')),

                            TextEntry::make('plant.name')
                                ->label('Plant')
                                ->placeholder('—'),

                            TextEntry::make('department.name')
                                ->label('Department')
                                ->placeholder('—'),

                            TextEntry::make('requester.name')
                                ->label('Requester')
                                ->placeholder('—'),
                        ]),

                        // ROW 2: Lokasi Asal | Lokasi Tujuan | Keperluan (span 4)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 6,
                        ])->schema([
                            TextEntry::make('originLocation.name')
                                ->label('Lokasi Asal')
                                ->placeholder('—')
                                ->columnSpan(1),

                            TextEntry::make('destinationLocation.name')
                                ->label('Lokasi Tujuan')
                                ->placeholder('—')
                                ->columnSpan(1),

                            TextEntry::make('needed_name')
                                ->label('Keperluan')
                                ->placeholder('—')
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ]),
                        ]),

                        // ROW 3: Alamat Asal | Alamat Tujuan (readonly multiline)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 2,
                        ])->schema([
                            TextEntry::make('originLocation.address')
                                ->label('Alamat')
                                ->placeholder('—'),

                            TextEntry::make('destinationLocation.address')
                                ->label('Alamat')
                                ->placeholder('—'),
                        ]),

                        // ROW 4: Tanggal Kebutuhan | Keterangan (span 5)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 6,
                        ])->schema([
                            TextEntry::make('date_needed')
                                ->label('Tanggal Kebutuhan')
                                ->date('d/m/Y')
                                ->placeholder('—')
                                ->columnSpan(1),

                            TextEntry::make('purpose')
                                ->label('Keterangan')
                                ->placeholder('—')
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'lg' => 5,
                                ]),
                        ]),

                        // ROW 5: Lampiran (full width)
                        TextEntry::make('attachments_list')
                            ->label('Lampiran')
                            ->state(function ($record): string {
                                if (! $record || $record->attachments->isEmpty()) {
                                    return 'Tidak ada lampiran';
                                }

                                return $record->attachments->pluck('file_name')->join(', ');
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 2: DETAIL BARANG / ASSET ────────────────────────
                Section::make('Detail Barang / Asset')
                    ->schema([
                        RepeatableEntry::make('sppbDetails')
                            ->label('')
                            ->schema([
                                TextEntry::make('barcode_confirmed')
                                    ->label('Jenis')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Asset' : 'Non Asset')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'info' : 'gray'),

                                TextEntry::make('reference_code')
                                    ->label('Kode')
                                    ->placeholder('—'),

                                TextEntry::make('item.name')
                                    ->label('Nama Barang')
                                    ->placeholder('—')
                                    ->visible(fn ($record): bool => ! $record?->barcode_confirmed),

                                TextEntry::make('asset.asset_name')
                                    ->label('Nama Asset')
                                    ->placeholder('—')
                                    ->visible(fn ($record): bool => (bool) $record?->barcode_confirmed),

                                TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->placeholder('—'),

                                TextEntry::make('unit.name')
                                    ->label('Satuan')
                                    ->placeholder('—'),

                                TextEntry::make('remarks')
                                    ->label('Keterangan / Spesifikasi')
                                    ->placeholder('—'),
                            ])
                            ->columns([
                                'default' => 2,
                                'sm' => 4,
                                'lg' => 7,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 3: WORKFLOW PERSETUJUAN ──────────────────────────
                Section::make('Workflow Persetujuan')
                    ->schema([
                        Placeholder::make('workflow_timeline_view')
                            ->hiddenLabel()
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                return SppbHeaderForm::renderWorkflowTimeline($record);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
