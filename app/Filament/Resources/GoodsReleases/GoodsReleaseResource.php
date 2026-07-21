<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases;

use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\EditGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\ListGoodsReleases;
use App\Models\GoodsRelease;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class GoodsReleaseResource extends Resource
{
    protected static ?string $model = GoodsRelease::class;

    protected static ?string $slug = 'goods-releases';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Surat Jalan';

    protected static ?string $modelLabel = 'Surat Jalan';

    protected static ?string $pluralModelLabel = 'Surat Jalan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // ─── PANEL 1: INFORMASI SURAT JALAN ─────────────────────────────
            Section::make('Informasi Surat Jalan')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 6,
                    ])->schema([
                        TextInput::make('release_number')
                            ->label('No Surat Jalan')
                            ->default(fn () => 'SJ-'.date('Ymd').'-'.rand(100, 999))
                            ->readOnly()
                            ->columnSpan(1),

                        DatePicker::make('delivery_date')
                            ->label('Tanggal *')
                            ->default(fn () => now()->toDateString())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('plant_name')
                            ->label('Plant')
                            ->readOnly()
                            ->default(fn () => auth()->user()?->plant?->name)
                            ->columnSpan(1),

                        TextInput::make('department_name')
                            ->label('Departemen')
                            ->readOnly()
                            ->default(fn () => auth()->user()?->department?->name)
                            ->columnSpan(1),

                        TextInput::make('created_by_name')
                            ->label('Pembuat Surat Jalan')
                            ->readOnly()
                            ->default(fn () => auth()->user()?->name)
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Status Pengiriman')
                            ->options([
                                'DRAFT' => 'Draft',
                                'RELEASED' => 'Dalam Pengiriman',
                                'RECEIVED' => 'Terkirim',
                                'CANCELLED' => 'Dibatalkan',
                            ])
                            ->default('DRAFT')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ]),
                ])
                ->columnSpanFull(),

            // ─── PANEL 2: INFORMASI SPPB ────────────────────────────────────
            Section::make('Informasi SPPB')
                ->schema([
                    Placeholder::make('sppb_select_css')
                        ->hiddenLabel()
                        ->content(new HtmlString('
                            <style>
                                .fi-badge-label .sppb-extra-info,
                                .fi-select-input-value-label .sppb-extra-info,
                                .fi-badge-label-ctn .sppb-extra-info {
                                    display: none !important;
                                }
                            </style>
                        '))
                        ->columnSpanFull(),

                    Select::make('sppbHeaders')
                        ->label('No SPPB')
                        ->allowHtml()
                        ->relationship('sppbHeaders', 'document_number', function ($query, Get $get) {
                            $user = auth()->user();
                            $query->where('status', 'APPROVED');

                            if ($user && ! $user->hasRole('super_admin')) {
                                $query->where('plant_id', $user->plant_id)
                                    ->where('department_id', $user->department_id);
                            }

                            $selectedIds = $get('sppbHeaders') ?? [];
                            if (! empty($selectedIds)) {
                                $firstSppb = SppbHeader::find($selectedIds[0]);
                                if ($firstSppb) {
                                    $query->where('origin_location_id', $firstSppb->origin_location_id)
                                        ->where('destination_location_id', $firstSppb->destination_location_id);
                                }
                            }
                        })
                        ->multiple()
                        ->searchable()
                        ->required()
                        ->live()
                        ->default(fn () => request()->query('sppb_header_id') ? [(int) request()->query('sppb_header_id')] : [])
                        ->disabled(fn ($record) => $record && $record->status !== 'DRAFT')
                        ->getSearchResultsUsing(function (string $search, Get $get) {
                            $user = auth()->user();
                            $query = SppbHeader::with('requester')
                                ->where('status', 'APPROVED')
                                ->where(function ($q) use ($search) {
                                    $q->where('document_number', 'like', "%{$search}%")
                                        ->orWhereHas('requester', fn ($rq) => $rq->where('name', 'like', "%{$search}%"))
                                        ->orWhere('needed_name', 'like', "%{$search}%");
                                });

                            if ($user && ! $user->hasRole('super_admin')) {
                                $query->where('plant_id', $user->plant_id)
                                    ->where('department_id', $user->department_id);
                            }

                            $selectedIds = $get('sppbHeaders') ?? [];
                            if (! empty($selectedIds)) {
                                $firstSppb = SppbHeader::find($selectedIds[0]);
                                if ($firstSppb) {
                                    $query->where('origin_location_id', $firstSppb->origin_location_id)
                                        ->where('destination_location_id', $firstSppb->destination_location_id);
                                }
                            }

                            return $query->get()
                                ->mapWithKeys(function ($record) {
                                    $html = '<span>['.e($record->document_number).']</span><span class="sppb-extra-info text-gray-500 font-normal"> - ['.e($record->requester?->name).'] - '.e($record->needed_name).'</span>';

                                    return [$record->id => $html];
                                })
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $record = SppbHeader::with('requester')->find($value);
                            if (! $record) {
                                return null;
                            }

                            return '<span>['.e($record->document_number).']</span><span class="sppb-extra-info text-gray-500 font-normal"> - ['.e($record->requester?->name).'] - '.e($record->needed_name).'</span>';
                        })
                        ->getOptionLabelsUsing(function (array $values) {
                            $records = SppbHeader::with('requester')->whereIn('id', $values)->get();

                            return $records->mapWithKeys(function ($record) {
                                $html = '<span>['.e($record->document_number).']</span><span class="sppb-extra-info text-gray-500 font-normal"> - ['.e($record->requester?->name).'] - '.e($record->needed_name).'</span>';

                                return [$record->id => $html];
                            })->toArray();
                        })
                        ->getOptionLabelFromRecordUsing(function (SppbHeader $record) {
                            return '<span>['.e($record->document_number).']</span><span class="sppb-extra-info text-gray-500 font-normal"> - ['.e($record->requester?->name).'] - '.e($record->needed_name).'</span>';
                        })
                        ->afterStateUpdated(function (Set $set, $state) {
                            if (empty($state)) {
                                $set('goodsReleaseItems', []);

                                return;
                            }

                            $details = SppbDetail::with(['unit', 'item', 'asset', 'sppbHeader.requester'])
                                ->whereIn('sppb_header_id', $state)
                                ->get()
                                ->sortBy(fn ($detail) => $detail->sppbHeader->document_number);

                            $items = [];
                            foreach ($details as $detail) {
                                $type = $detail->asset_id ? 'Asset' : 'Non Asset';
                                $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';
                                $items[] = [
                                    'sppb_detail_id' => $detail->id,
                                    'item_type' => $type,
                                    'barcode_code' => $code,
                                    'item_name' => $detail->item_asset_name,
                                    'quantity_requested' => $detail->quantity,
                                    'quantity_released' => $detail->quantity,
                                    'unit_name' => $detail->unit?->name,
                                    'condition_on_release' => $detail->remarks,
                                ];
                            }
                            $set('goodsReleaseItems', $items);
                        })
                        ->columnSpanFull(),

                    Placeholder::make('selected_sppb_table')
                        ->label('Daftar SPPB Terpilih')
                        ->content(function (Get $get): HtmlString {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            if (empty($sppbIds)) {
                                return new HtmlString('<p class="text-xs text-gray-500 italic">Belum ada SPPB yang dipilih</p>');
                            }

                            $sppbs = SppbHeader::with(['requester'])->whereIn('id', $sppbIds)->get();

                            $html = '<div class="overflow-x-auto"><table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-white/5">';
                            $html .= '<thead class="bg-gray-50 dark:bg-white/5"><tr>';
                            $html .= '<th class="px-3 py-2 font-semibold">No</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">SPPB</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">Tanggal</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">Status</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">Pemohon SPPB</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">Keperluan</th>';
                            $html .= '<th class="px-3 py-2 font-semibold">Keterangan</th>';
                            $html .= '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-white/5">';

                            foreach ($sppbs as $index => $sppb) {
                                $html .= '<tr>';
                                $html .= '<td class="px-3 py-2">'.($index + 1).'</td>';
                                $html .= '<td class="px-3 py-2 font-medium text-gray-900 dark:text-white">'.e($sppb->document_number).'</td>';
                                $html .= '<td class="px-3 py-2">'.e($sppb->request_date?->format('d/m/Y')).'</td>';
                                $html .= '<td class="px-3 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-150 text-green-800 dark:bg-green-950 dark:text-green-300">'.e($sppb->status).'</span></td>';
                                $html .= '<td class="px-3 py-2">'.e($sppb->requester?->name).'</td>';
                                $html .= '<td class="px-3 py-2">'.e($sppb->needed_name).'</td>';
                                $html .= '<td class="px-3 py-2 break-all">'.e($sppb->purpose).'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</tbody></table></div>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // ─── PANEL 3: INFORMASI PENGIRIMAN ──────────────────────────────
            Section::make('Informasi Pengiriman')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])->schema([
                        TextInput::make('driver_name')
                            ->label('Nama Pengemudi *')
                            ->required(),

                        TextInput::make('vehicle_number')
                            ->label('No Kendaraan *')
                            ->required(),

                        TextInput::make('expedition_name')
                            ->label('Ekspedisi *')
                            ->required(),

                        DatePicker::make('delivery_date_display')
                            ->label('Tanggal Pengiriman *')
                            ->readOnly()
                            ->placeholder(fn (Get $get) => $get('delivery_date') ? Carbon::parse($get('delivery_date'))->format('d/m/Y') : 'Otomatis'),
                    ]),

                    Grid::make([
                        'default' => 1,
                        'lg' => 2,
                    ])->schema([
                        TextInput::make('sender_name')
                            ->label('Lokasi Asal')
                            ->required()
                            ->readOnly()
                            ->default(function (Get $get) {
                                $sppbIds = $get('sppbHeaders') ?? [];
                                if (! empty($sppbIds)) {
                                    return SppbHeader::find($sppbIds[0])?->originLocation?->name;
                                }

                                return null;
                            }),

                        TextInput::make('receiver_name')
                            ->label('Lokasi Tujuan')
                            ->required()
                            ->readOnly()
                            ->default(function (Get $get) {
                                $sppbIds = $get('sppbHeaders') ?? [];
                                if (! empty($sppbIds)) {
                                    return SppbHeader::find($sppbIds[0])?->destinationLocation?->name;
                                }

                                return null;
                            }),

                        Textarea::make('sender_address')
                            ->label('Alamat Asal')
                            ->rows(3)
                            ->required()
                            ->readOnly()
                            ->default(function (Get $get) {
                                $sppbIds = $get('sppbHeaders') ?? [];
                                if (! empty($sppbIds)) {
                                    return SppbHeader::find($sppbIds[0])?->originLocation?->address;
                                }

                                return null;
                            }),

                        Textarea::make('receiver_address')
                            ->label('Alamat Tujuan')
                            ->rows(3)
                            ->required()
                            ->readOnly()
                            ->default(function (Get $get) {
                                $sppbIds = $get('sppbHeaders') ?? [];
                                if (! empty($sppbIds)) {
                                    return SppbHeader::find($sppbIds[0])?->destinationLocation?->address;
                                }

                                return null;
                            }),
                    ]),

                    Textarea::make('notes')
                        ->label('Keterangan Pengiriman')
                        ->rows(4)
                        ->placeholder('Isi keterangan pengiriman...')
                        ->disabled(fn ($record) => $record && $record->status !== 'DRAFT')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // ─── PANEL 4: DAFTAR BARANG ──────────────────────────────────────
            Section::make('Daftar Barang')
                ->schema([
                    Repeater::make('goodsReleaseItems')
                        ->relationship('goodsReleaseItems')
                        ->label('Barang Dikirim')
                        ->addActionLabel('Tambah Barang')
                        ->addable(fn ($record) => ! $record || $record->status === 'DRAFT')
                        ->deletable(fn ($record) => ! $record || $record->status === 'DRAFT')
                        ->reorderable(false)
                        ->disabled(fn ($record) => $record && $record->status !== 'DRAFT')
                        ->columns(12)
                        ->default(function () {
                            $sppbId = request()->query('sppb_header_id');
                            if ($sppbId) {
                                $details = SppbDetail::with(['unit', 'item', 'asset', 'sppbHeader.requester'])
                                    ->where('sppb_header_id', $sppbId)
                                    ->get()
                                    ->sortBy(fn ($detail) => $detail->sppbHeader->document_number);

                                $items = [];
                                foreach ($details as $detail) {
                                    $type = $detail->asset_id ? 'Asset' : 'Non Asset';
                                    $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';
                                    $items[] = [
                                        'sppb_detail_id' => $detail->id,
                                        'item_type' => $type,
                                        'barcode_code' => $code,
                                        'item_name' => $detail->item_asset_name,
                                        'quantity_requested' => $detail->quantity,
                                        'quantity_released' => $detail->quantity,
                                        'unit_name' => $detail->unit?->name,
                                        'condition_on_release' => $detail->remarks,
                                    ];
                                }

                                return $items;
                            }

                            return [];
                        })
                        ->schema([
                            Placeholder::make('sppb_info')
                                ->hiddenLabel()
                                ->content(function (Get $get) {
                                    $detailId = $get('sppb_detail_id');
                                    if (! $detailId) {
                                        return null;
                                    }
                                    $detail = SppbDetail::with('sppbHeader.requester')->find($detailId);
                                    if (! $detail || ! $detail->sppbHeader) {
                                        return null;
                                    }

                                    // Grouping check: only show header for the first item of this SPPB
                                    $items = $get('../../goodsReleaseItems') ?? [];
                                    $sppbHeaderId = $detail->sppb_header_id;
                                    $firstItemWithThisSppb = null;
                                    foreach ($items as $item) {
                                        if (isset($item['sppb_detail_id'])) {
                                            $d = SppbDetail::find($item['sppb_detail_id']);
                                            if ($d && $d->sppb_header_id === $sppbHeaderId) {
                                                $firstItemWithThisSppb = $item['sppb_detail_id'];
                                                break;
                                            }
                                        }
                                    }

                                    if ($detailId !== $firstItemWithThisSppb) {
                                        return null;
                                    }

                                    $label = "[{$detail->sppbHeader->document_number}]-[{$detail->sppbHeader->requester?->name}] - {$detail->sppbHeader->needed_name}";

                                    return new HtmlString("<div class='bg-primary-50 dark:bg-primary-950/20 border-l-4 border-primary-500 px-4 py-2.5 rounded font-bold text-sm text-primary-700 dark:text-primary-300 mb-4 w-full flex items-center gap-2'>📦 SPPB: {$label}</div>");
                                })
                                ->columnSpanFull(),

                            Placeholder::make('row_no')
                                ->label('No.')
                                ->content(function ($component, Get $get) {
                                    $items = $get('../../goodsReleaseItems') ?? [];
                                    $statePath = $component->getContainer()->getStatePath();
                                    $parts = explode('.', $statePath);
                                    $keyIndex = array_search('goodsReleaseItems', $parts);
                                    if ($keyIndex !== false && isset($parts[$keyIndex + 1])) {
                                        $itemKey = $parts[$keyIndex + 1];
                                        $keys = array_keys($items);
                                        $position = array_search($itemKey, $keys);
                                        if ($position !== false) {
                                            return $position + 1;
                                        }
                                    }

                                    $currentDetailId = $get('sppb_detail_id');
                                    if ($currentDetailId) {
                                        $index = 1;
                                        foreach ($items as $item) {
                                            if (is_array($item) && isset($item['sppb_detail_id']) && $item['sppb_detail_id'] == $currentDetailId) {
                                                return $index;
                                            }
                                            $index++;
                                        }
                                    }

                                    return 1;
                                })
                                ->columnSpan(1),

                            Select::make('sppb_detail_id')
                                ->label('Barang SPPB')
                                ->required()
                                ->live()
                                ->searchable()
                                ->disabled(fn ($record) => $record && $record->status !== 'DRAFT')
                                ->options(function (Get $get) {
                                    $sppbIds = $get('../../sppbHeaders') ?? [];
                                    if (empty($sppbIds)) {
                                        return [];
                                    }

                                    $details = SppbDetail::with(['unit', 'item', 'asset', 'sppbHeader'])
                                        ->whereIn('sppb_header_id', $sppbIds)
                                        ->get();

                                    $currentDetailId = $get('sppb_detail_id');
                                    $allSelected = collect($get('../../goodsReleaseItems') ?? [])
                                        ->pluck('sppb_detail_id')
                                        ->filter()
                                        ->reject(fn ($id) => $id == $currentDetailId)
                                        ->toArray();

                                    return $details
                                        ->reject(fn ($detail) => in_array($detail->id, $allSelected))
                                        ->mapWithKeys(function ($detail) {
                                            $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';

                                            return [$detail->id => "[{$detail->sppbHeader?->document_number}] {$detail->item_asset_name} ({$code})"];
                                        })
                                        ->toArray();
                                })
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if (! $state) {
                                        $set('item_type', null);
                                        $set('barcode_code', null);
                                        $set('quantity_requested', null);
                                        $set('quantity_released', null);
                                        $set('unit_name', null);
                                        $set('condition_on_release', null);

                                        return;
                                    }

                                    $detail = SppbDetail::with(['unit', 'item', 'asset'])->find($state);
                                    if (! $detail) {
                                        return;
                                    }

                                    $type = $detail->asset_id ? 'Asset' : 'Non Asset';
                                    $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';

                                    $set('item_type', $type);
                                    $set('barcode_code', $code);
                                    $set('quantity_requested', $detail->quantity);
                                    $set('quantity_released', $detail->quantity);
                                    $set('unit_name', $detail->unit?->name);
                                    $set('condition_on_release', $detail->remarks);
                                })
                                ->columnSpan(3),

                            TextInput::make('item_type')
                                ->label('Jenis')
                                ->readOnly()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (TextInput $component, Get $get) {
                                    $detailId = $get('sppb_detail_id');
                                    if ($detailId) {
                                        $detail = SppbDetail::find($detailId);
                                        $component->state($detail?->asset_id ? 'Asset' : 'Non Asset');
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('barcode_code')
                                ->label('Barcode/Kode')
                                ->readOnly()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (TextInput $component, Get $get) {
                                    $detailId = $get('sppb_detail_id');
                                    if ($detailId) {
                                        $detail = SppbDetail::with(['asset', 'item'])->find($detailId);
                                        $code = $detail?->asset?->barcode ?? $detail?->item?->code ?? $detail?->reference_code ?? '-';
                                        $component->state($code);
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('quantity_requested')
                                ->label('Qty SPPB')
                                ->numeric()
                                ->readOnly()
                                ->columnSpan(1),

                            TextInput::make('quantity_released')
                                ->label('Qty Kirim')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->columnSpan(1),

                            TextInput::make('unit_name')
                                ->label('Satuan')
                                ->readOnly()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (TextInput $component, Get $get) {
                                    $detailId = $get('sppb_detail_id');
                                    if ($detailId) {
                                        $detail = SppbDetail::with('unit')->find($detailId);
                                        $component->state($detail?->unit?->name);
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('condition_on_release')
                                ->label('Keterangan')
                                ->placeholder('Kondisi barang saat keluar...')
                                ->columnSpan(3),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Hidden::make('is_manual')
                ->default(fn () => request()->query('is_manual') === '1'),

            Hidden::make('created_by_id')
                ->default(fn () => auth()->id()),

            Hidden::make('sppb_header_id')
                ->dehydrated()
                ->dehydrateStateUsing(function (Get $get) {
                    $sppbs = $get('sppbHeaders') ?? [];

                    return ! empty($sppbs) ? $sppbs[0] : null;
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('release_number')
                    ->label('No. Surat Jalan')
                    ->searchable(),
                TextColumn::make('sppbHeader.document_number')
                    ->label('No. SPPB')
                    ->searchable(),
                TextColumn::make('driver_name')
                    ->label('Pengemudi')
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT' => 'gray',
                        'RELEASED' => 'info',
                        'RECEIVED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'DRAFT' => 'Draft',
                        'RELEASED' => 'Dalam Pengiriman',
                        'RECEIVED' => 'Terkirim',
                        'CANCELLED' => 'Dibatalkan',
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->whereHas('sppbHeader', function ($sppbQuery) use ($user) {
            $sppbQuery->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('current_approver_id', $user->id)
                    ->orWhereExists(function ($rawQuery) use ($user) {
                        $rawQuery->select(DB::raw(1))
                            ->from('document_accesses')
                            ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                            ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                            ->where('document_accesses.user_id', $user->id)
                            ->where('document_accesses.module', 'goods_release')
                            ->where('document_accesses.can_view', true);
                    });
            });
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoodsReleases::route('/'),
            'create' => CreateGoodsRelease::route('/create'),
            'edit' => EditGoodsRelease::route('/{record}/edit'),
        ];
    }
}
