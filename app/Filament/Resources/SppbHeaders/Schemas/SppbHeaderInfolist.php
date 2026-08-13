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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class SppbHeaderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── SECTION 1: INFORMASI UTAMA SPPB ──────────────────────────────
                Section::make('Informasi Utama SPPB')
                    ->schema([
                        // ROW 1: No. SPPB | Tgl Permintaan | Status
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])->schema([
                            TextEntry::make('document_number')
                                ->label('No. SPPB')
                                ->placeholder('—'),

                            TextEntry::make('request_date')
                                ->label('Tanggal Permintaan')
                                ->date('d/m/Y')
                                ->placeholder('—')
                                ->icon('heroicon-m-calendar'),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn ($state): string => $state instanceof SppbStatus
                                    ? $state->label()
                                    : (SppbStatus::tryFrom($state)?->label() ?? $state))
                                ->color(fn ($state): string => $state instanceof SppbStatus
                                    ? $state->color()
                                    : (SppbStatus::tryFrom($state)?->color() ?? 'gray')),
                        ]),

                        // ROW 2: Plant | Department | Pemohon (Requester)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])->schema([
                            TextEntry::make('plant.name')
                                ->label('Plant')
                                ->placeholder('—'),

                            TextEntry::make('department.name')
                                ->label('Department')
                                ->placeholder('—'),

                            TextEntry::make('requester.name')
                                ->label('Pemohon')
                                ->placeholder('—'),
                        ]),

                        // ROW 3: Keperluan (80%) | Tanggal Dibutuhkan (20%)
                        Grid::make([
                            'default' => 1,
                            'lg' => 10,
                        ])->schema([
                            TextEntry::make('needed_name')
                                ->label('Keperluan')
                                ->placeholder('—')
                                ->columnSpan(8),

                            TextEntry::make('date_needed')
                                ->label('Tanggal Dibutuhkan')
                                ->date('d/m/Y')
                                ->placeholder('—')
                                ->icon('heroicon-m-calendar')
                                ->columnSpan(2),
                        ]),

                        // ROW 4: Lokasi Asal | Lokasi Tujuan
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextEntry::make('originLocation.name')
                                ->label('Lokasi Asal')
                                ->placeholder('—'),

                            TextEntry::make('destinationLocation.name')
                                ->label('Lokasi Tujuan')
                                ->placeholder('—'),
                        ]),

                        // ROW 5: Alamat Asal | Alamat Tujuan
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextEntry::make('originLocation.address')
                                ->label('Alamat Asal')
                                ->placeholder('—'),

                            TextEntry::make('destinationLocation.address')
                                ->label('Alamat Tujuan')
                                ->placeholder('—'),
                        ]),

                        // ROW 6: Keterangan (Full Width)
                        TextEntry::make('purpose')
                            ->label('Keterangan')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        // ROW 7: Lampiran (Full Width)
                        Placeholder::make('attachments_list')
                            ->label('Lampiran')
                            ->content(function ($record): HtmlString {
                                if (! $record || $record->attachments->isEmpty()) {
                                    return new HtmlString('<p class="text-xs text-gray-500 italic">Tidak ada lampiran</p>');
                                }

                                $html = '<ul class="divide-y divide-gray-200 dark:divide-white/5 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-gray-900 shadow-sm overflow-hidden">';
                                foreach ($record->attachments as $attachment) {
                                    $previewUrl = URL::signedRoute('attachments.viewer', ['attachment' => $attachment->uuid]);
                                    $downloadUrl = URL::signedRoute('attachments.download', ['attachment' => $attachment->uuid]);

                                    $html .= '<li class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-white/5">';

                                    // File name
                                    $html .= '<div class="flex items-center space-x-2 min-w-0 flex-1 mr-2">';
                                    $html .= '<span class="text-xs text-gray-700 dark:text-gray-300 truncate font-medium" title="'.e($attachment->original_name).'">'.e($attachment->original_name).'</span>';
                                    $html .= '</div>';

                                    // Action links
                                    $html .= '<div class="flex items-center space-x-1.5 flex-shrink-0">';
                                    $html .= '<a href="'.$previewUrl.'" target="_blank" class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/30 px-1.5 py-0.5 rounded hover:bg-primary-100 transition-colors">Preview</a>';
                                    $html .= '<a href="'.$downloadUrl.'" class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-1.5 py-0.5 rounded hover:bg-gray-100 transition-colors">Download</a>';
                                    $html .= '</div>';

                                    $html .= '</li>';
                                }
                                $html .= '</ul>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 2A: DETAIL ASET (TERDAFTAR / BER-BARCODE) ───────
                Section::make('Detail Aset (Terdaftar / Ber-Barcode)')
                    ->schema([
                        // Header Grid (Hanya tampil di Desktop)
                        Grid::make([
                            'default' => 1,
                            'sm' => 4,
                            'lg' => 16,
                        ])
                            ->schema([
                                Placeholder::make('hdr_jenis_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Jenis</span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_barcode_kode_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Barcode/Kode <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(2),

                                Placeholder::make('hdr_nama_aset_barang_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Nama Aset/Barang <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 5 : 6),

                                Placeholder::make('hdr_qty_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Qty <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_satuan_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Satuan <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(2),

                                Placeholder::make('hdr_remarks_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Keterangan / Spesifikasi</span>'))
                                    ->columnSpan(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 3 : 4),

                                Placeholder::make('hdr_status_pengiriman_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Status Pengiriman</span>'))
                                    ->columnSpan(2)
                                    ->visible(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED'])),
                            ])
                            ->extraAttributes(['class' => 'hidden lg:grid mb-2']),

                        RepeatableEntry::make('sppbDetailsAsset')
                            ->hiddenLabel()
                            ->getStateUsing(fn ($record) => $record?->sppbDetails()->where('barcode_confirmed', true)->get())
                            ->schema([
                                TextEntry::make('barcode_confirmed')
                                    ->label('Jenis')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Asset' : 'Non Asset')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'info' : 'gray')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(1),

                                TextEntry::make('reference_code')
                                    ->label('Barcode/Kode')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(2),

                                TextEntry::make('item_asset_name')
                                    ->label('Nama Aset/Barang')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 5 : 6),

                                TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(1),

                                TextEntry::make('unit.name')
                                    ->label('Satuan')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(2),

                                TextEntry::make('remarks')
                                    ->label('Keterangan / Spesifikasi')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 3 : 4),

                                TextEntry::make('delivery_status_display')
                                    ->label('Status Pengiriman')
                                    ->getStateUsing(fn ($record): string => $record?->delivery_status_label ?? 'Belum Dikirim')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Draft Surat Jalan' => 'gray',
                                        'Pengiriman Penuh' => 'info',
                                        'Pengiriman Sebagian' => 'warning',
                                        'Diterima / Terkirim', 'Terkirim' => 'success',
                                        'Diterima Sebagian' => 'warning',
                                        default => 'gray',
                                    })
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(2)
                                    ->visible(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED'])),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 4,
                                'lg' => 16,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->sppbDetails()->where('barcode_confirmed', true)->exists()),

                // ─── SECTION 2B: DETAIL BARANG (NON-ASSET / INPUT BEBAS) ──────
                Section::make('Detail Barang (Non-Asset / Input Bebas)')
                    ->schema([
                        // Header Grid (Hanya tampil di Desktop)
                        Grid::make([
                            'default' => 1,
                            'sm' => 4,
                            'lg' => 16,
                        ])
                            ->schema([
                                Placeholder::make('hdr_jenis_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Jenis</span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_nama_aset_barang_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Nama Aset/Barang <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 7 : 8),

                                Placeholder::make('hdr_qty_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Qty <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_satuan_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Satuan <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(2),

                                Placeholder::make('hdr_remarks_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Keterangan / Spesifikasi</span>'))
                                    ->columnSpan(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 3 : 4),

                                Placeholder::make('hdr_status_pengiriman_non_asset')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Status Pengiriman</span>'))
                                    ->columnSpan(2)
                                    ->visible(fn ($record) => in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED'])),
                            ])
                            ->extraAttributes(['class' => 'hidden lg:grid mb-2']),

                        RepeatableEntry::make('sppbDetailsNonAsset')
                            ->hiddenLabel()
                            ->getStateUsing(fn ($record) => $record?->sppbDetails()->where('barcode_confirmed', false)->get())
                            ->schema([
                                TextEntry::make('barcode_confirmed')
                                    ->label('Jenis')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Asset' : 'Non Asset')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'info' : 'gray')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(1),

                                TextEntry::make('item_asset_name')
                                    ->label('Nama Aset/Barang')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 7 : 8),

                                TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(1),

                                TextEntry::make('unit.name')
                                    ->label('Satuan')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(2),

                                TextEntry::make('remarks')
                                    ->label('Keterangan / Spesifikasi')
                                    ->placeholder('—')
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ? 3 : 4),

                                TextEntry::make('delivery_status_display')
                                    ->label('Status Pengiriman')
                                    ->getStateUsing(fn ($record): string => $record?->delivery_status_label ?? 'Belum Dikirim')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Draft Surat Jalan' => 'gray',
                                        'Pengiriman Penuh' => 'info',
                                        'Pengiriman Sebagian' => 'warning',
                                        'Diterima / Terkirim', 'Terkirim' => 'success',
                                        'Diterima Sebagian' => 'warning',
                                        default => 'gray',
                                    })
                                    ->extraEntryWrapperAttributes(['class' => 'lg:[&_.fi-in-entry-label-col]:hidden'])
                                    ->columnSpan(2)
                                    ->visible(fn ($record) => in_array($record?->sppbHeader?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED'])),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 4,
                                'lg' => 16,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->sppbDetails()->where('barcode_confirmed', false)->exists()),

                // ─── SECTION 3: DAFTAR SURAT JALAN TERKAIT ───────────────────
                Section::make('Daftar Surat Jalan Terkait')
                    ->schema([
                        Placeholder::make('goods_release_list_view')
                            ->hiddenLabel()
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                return SppbHeaderForm::renderGoodsReleaseList($record);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record && (
                        in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ||
                        $record->goodsReleases()->exists() ||
                        $record->goodsReleasesPivot()->exists()
                    )),

                // ─── SECTION 4: WORKFLOW PERSETUJUAN ──────────────────────────
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
