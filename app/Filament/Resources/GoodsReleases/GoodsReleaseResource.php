<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases;

use App\Enums\GoodsReleaseStatus;
use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\EditGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\ListGoodsReleases;
use App\Filament\Resources\GoodsReleases\Pages\ViewGoodsRelease;
use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

    public static function isPureManual(Get $get, $record): bool
    {
        $isManual = (bool) ($get('is_manual') ?? $get('../is_manual') ?? $get('../../is_manual') ?? $get('../../../is_manual') ?? $record?->is_manual);
        if (! $isManual) {
            return false;
        }

        $sppbIds = $get('sppbHeaders') ?? $get('../../sppbHeaders') ?? $get('../../../sppbHeaders') ?? [];
        $sppbId = $get('sppb_header_id') ?? $get('../../sppb_header_id') ?? request()->query('sppb_header_id') ?? $record?->sppb_header_id;
        $hasSppbRelation = $record && ($record->sppbHeaders?->isNotEmpty() || $record->sppb_header_id);

        return empty($sppbIds) && empty($sppbId) && ! $hasSppbRelation;
    }

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
                            ->label(fn (Get $get) => $get('is_manual') ? 'No. Referensi (Sistem)' : 'No. Surat Jalan')
                            ->placeholder('(Otomatis saat disimpan)')
                            ->readOnly()
                            ->columnSpan(1),

                        TextInput::make('manual_release_number')
                            ->label('No. Surat Jalan Manual *')
                            ->required()
                            ->visible(fn (Get $get) => (bool) $get('is_manual'))
                            ->dehydrated(fn (Get $get) => (bool) $get('is_manual'))
                            ->maxLength(50)
                            ->columnSpan(1),

                        DatePicker::make('surat_jalan_date')
                            ->label('Tanggal *')
                            ->default(fn () => now()->toDateString())
                            ->formatStateUsing(fn ($record, $state) => $record?->created_at?->format('Y-m-d') ?? ($state ?? now()->toDateString()))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->readOnly()
                            ->dehydrated(false)
                            ->columnSpan(1),

                        TextInput::make('plant_name')
                            ->label('Plant')
                            ->readOnly()
                            ->formatStateUsing(function ($record, Get $get) {
                                if ($record) {
                                    return $record->sppbHeader?->plant?->name ?? $record->createdBy?->plant?->name ?? auth()->user()?->plant?->name;
                                }
                                $sppbIds = $get('sppbHeaders') ?? [];
                                $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);
                                if ($sppbId) {
                                    return SppbHeader::with('plant')->find($sppbId)?->plant?->name ?? auth()->user()?->plant?->name;
                                }

                                return auth()->user()?->plant?->name;
                            })
                            ->columnSpan(1),

                        TextInput::make('department_name')
                            ->label('Departemen')
                            ->readOnly()
                            ->formatStateUsing(function ($record, Get $get) {
                                if ($record) {
                                    return $record->sppbHeader?->department?->name ?? $record->createdBy?->department?->name ?? auth()->user()?->department?->name;
                                }
                                $sppbIds = $get('sppbHeaders') ?? [];
                                $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);
                                if ($sppbId) {
                                    return SppbHeader::with('department')->find($sppbId)?->department?->name ?? auth()->user()?->department?->name;
                                }

                                return auth()->user()?->department?->name;
                            })
                            ->columnSpan(1),

                        TextInput::make('created_by_name')
                            ->label('Pembuat Surat Jalan')
                            ->readOnly()
                            ->formatStateUsing(fn ($record) => $record?->createdBy?->name ?? auth()->user()?->name)
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Status Pengiriman')
                            ->options(collect(GoodsReleaseStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray())
                            ->default('DRAFT')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ]),
                ])
                ->columnSpanFull(),

            // ─── PANEL 2: INFORMASI SPPB ────────────────────────────────────
            Section::make('Dokumen Referensi SPPB')
                ->hidden(fn (Get $get, $record) => static::isPureManual($get, $record))
                ->schema([
                    Placeholder::make('sppb_select_css')
                        ->hiddenLabel()
                        ->hidden(fn (string $operation) => $operation === 'view')
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
                        ->hidden(fn (string $operation) => $operation === 'view')
                        ->allowHtml()
                        ->relationship('sppbHeaders', 'document_number', function ($query, Get $get) {
                            $user = auth()->user();
                            $query->whereIn('status', ['APPROVED', 'RELEASE_IN_PROGRESS']);

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
                                ->whereIn('status', ['APPROVED', 'RELEASE_IN_PROGRESS'])
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
                                $set('sender_name', null);
                                $set('receiver_name', null);
                                $set('sender_address', null);
                                $set('receiver_address', null);

                                return;
                            }

                            $firstSppb = SppbHeader::with(['originLocation', 'destinationLocation'])->find($state[0]);
                            if ($firstSppb) {
                                $set('sender_name', $firstSppb->originLocation?->name);
                                $set('receiver_name', $firstSppb->destinationLocation?->name);
                                $set('sender_address', $firstSppb->originLocation?->address);
                                $set('receiver_address', $firstSppb->destinationLocation?->address);
                            }

                            $details = SppbDetail::with(['unit', 'item', 'asset', 'sppbHeader.requester'])
                                ->whereIn('sppb_header_id', $state)
                                ->get()
                                ->sortBy(fn ($detail) => $detail->sppbHeader->document_number);

                            $items = [];
                            foreach ($details as $detail) {
                                $alreadyReleased = (float) GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                                    ->whereHas('goodsRelease', fn ($q) => $q->where('status', '!=', 'CANCELLED'))
                                    ->sum('quantity_released');

                                $remainingQty = max(0.0, (float) $detail->quantity - $alreadyReleased);

                                if ($remainingQty <= 0) {
                                    continue;
                                }

                                $type = $detail->asset_id ? 'Asset' : 'Non Asset';
                                $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';
                                $items[] = [
                                    'sppb_detail_id' => $detail->id,
                                    'item_type' => $type,
                                    'barcode_code' => $code,
                                    'item_name' => $detail->item_asset_name,
                                    'quantity_requested' => $remainingQty,
                                    'quantity_released' => $remainingQty,
                                    'unit_name' => $detail->unit?->name,
                                    'condition_on_release' => $detail->remarks,
                                ];
                            }
                            $set('goodsReleaseItems', $items);
                        })
                        ->columnSpanFull(),

                    Placeholder::make('selected_sppb_table')
                        ->label('Daftar SPPB Terpilih')
                        ->content(function (Get $get, $record): HtmlString {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            if (empty($sppbIds) && $record) {
                                $sppbIds = $record->sppbHeaders?->pluck('id')?->toArray() ?? [];
                                if (empty($sppbIds) && $record->sppb_header_id) {
                                    $sppbIds = [$record->sppb_header_id];
                                }
                            }

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

                        DatePicker::make('delivery_date')
                            ->label('Tanggal Pengiriman *')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(fn () => now()->toDateString()),
                    ]),

                    Hidden::make('sender_name')
                        ->default(function (Get $get) {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);

                            return $sppbId ? SppbHeader::with('originLocation')->find($sppbId)?->originLocation?->name : null;
                        }),

                    Hidden::make('sender_address')
                        ->default(function (Get $get) {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);

                            return $sppbId ? SppbHeader::with('originLocation')->find($sppbId)?->originLocation?->address : null;
                        }),

                    Hidden::make('receiver_name')
                        ->default(function (Get $get) {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);

                            return $sppbId ? SppbHeader::with('destinationLocation')->find($sppbId)?->destinationLocation?->name : null;
                        }),

                    Hidden::make('receiver_address')
                        ->default(function (Get $get) {
                            $sppbIds = $get('sppbHeaders') ?? [];
                            $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);

                            return $sppbId ? SppbHeader::with('destinationLocation')->find($sppbId)?->destinationLocation?->address : null;
                        }),

                    Grid::make([
                        'default' => 1,
                        'lg' => 2,
                    ])
                        ->hidden(fn (Get $get, $record) => ! static::isPureManual($get, $record))
                        ->schema([
                            TextInput::make('sender_name')
                                ->label('Lokasi Asal / Pengirim *')
                                ->required()
                                ->placeholder('misal: Gudang Utama Sidoarjo'),

                            TextInput::make('receiver_name')
                                ->label('Lokasi Tujuan / Penerima *')
                                ->required()
                                ->placeholder('misal: PT Vendor Repair Jaya'),

                            Textarea::make('sender_address')
                                ->label('Alamat Asal')
                                ->rows(3)
                                ->placeholder('Alamat pengirim...'),

                            Textarea::make('receiver_address')
                                ->label('Alamat Tujuan')
                                ->rows(3)
                                ->placeholder('Alamat tujuan...'),
                        ]),

                    Grid::make([
                        'default' => 1,
                        'lg' => 2,
                    ])
                        ->hidden(fn (Get $get, $record) => static::isPureManual($get, $record))
                        ->schema([
                            Placeholder::make('origin_location_and_address')
                                ->label('Lokasi & Alamat Asal')
                                ->content(function (Get $get): HtmlString {
                                    $name = $get('sender_name');
                                    $address = $get('sender_address');

                                    if (empty($name) && empty($address)) {
                                        $sppbIds = $get('sppbHeaders') ?? [];
                                        $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);
                                        if ($sppbId) {
                                            $sppb = SppbHeader::with('originLocation')->find($sppbId);
                                            $name = $sppb?->originLocation?->name;
                                            $address = $sppb?->originLocation?->address;
                                        }
                                    }

                                    if (empty($name) && empty($address)) {
                                        return new HtmlString('<div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-3 text-xs text-gray-500 italic">- Belum ada SPPB terpilih -</div>');
                                    }

                                    $html = '<div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-3 text-xs">';
                                    $html .= '<div class="font-bold text-gray-900 dark:text-white text-sm">'.e($name ?? '-').'</div>';
                                    if (! empty($address)) {
                                        $html .= '<div class="text-gray-600 dark:text-gray-400 mt-1">'.nl2br(e($address)).'</div>';
                                    }
                                    $html .= '</div>';

                                    return new HtmlString($html);
                                }),

                            Placeholder::make('destination_location_and_address')
                                ->label('Lokasi & Alamat Tujuan')
                                ->content(function (Get $get): HtmlString {
                                    $name = $get('receiver_name');
                                    $address = $get('receiver_address');

                                    if (empty($name) && empty($address)) {
                                        $sppbIds = $get('sppbHeaders') ?? [];
                                        $sppbId = request()->query('sppb_header_id') ?? ($sppbIds[0] ?? null);
                                        if ($sppbId) {
                                            $sppb = SppbHeader::with('destinationLocation')->find($sppbId);
                                            $name = $sppb?->destinationLocation?->name;
                                            $address = $sppb?->destinationLocation?->address;
                                        }
                                    }

                                    if (empty($name) && empty($address)) {
                                        return new HtmlString('<div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-3 text-xs text-gray-500 italic">- Belum ada SPPB terpilih -</div>');
                                    }

                                    $html = '<div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-3 text-xs">';
                                    $html .= '<div class="font-bold text-gray-900 dark:text-white text-sm">'.e($name ?? '-').'</div>';
                                    if (! empty($address)) {
                                        $html .= '<div class="text-gray-600 dark:text-gray-400 mt-1">'.nl2br(e($address)).'</div>';
                                    }
                                    $html .= '</div>';

                                    return new HtmlString($html);
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
                    Placeholder::make('goods_release_items_table')
                        ->hiddenLabel()
                        ->hidden(fn (string $operation) => $operation !== 'view')
                        ->content(function ($record) {
                            if (! $record) {
                                return null;
                            }

                            $items = $record->goodsReleaseItems()->with(['sppbDetail.unit', 'sppbDetail.item', 'sppbDetail.asset'])->get();
                            if ($items->isEmpty()) {
                                return new HtmlString('<p class="text-sm text-gray-500 italic p-4">Tidak ada barang</p>');
                            }

                            $isPureManualRecord = $record->is_manual && empty($record->sppb_header_id) && ($record->sppbHeaders?->isEmpty() ?? true);
                            if ($isPureManualRecord) {
                                $html = '<div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-lg">';
                                $html .= '<table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-white/10">';
                                $html .= '<thead class="bg-gray-50 dark:bg-white/5">';
                                $html .= '<tr>';
                                $html .= '<th class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10" width="5%">No</th>';
                                $html .= '<th class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10" width="10%">Jenis</th>';
                                $html .= '<th class="px-3 py-2.5 font-semibold border-r border-gray-200 dark:border-white/10" width="45%">Nama Barang / Aset</th>';
                                $html .= '<th class="px-3 py-2.5 font-semibold text-right border-r border-gray-200 dark:border-white/10" width="12%">Qty Kirim</th>';
                                $html .= '<th class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10" width="8%">Satuan</th>';
                                $html .= '<th class="px-3 py-2.5 font-semibold" width="20%">Keterangan</th>';
                                $html .= '</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-gray-900">';

                                $totalKirimManual = 0;
                                foreach ($items as $index => $item) {
                                    $itemTitle = $item->item_name ?? $item->sppbDetail?->item_asset_name ?? '-';
                                    $codeVal = $item->barcode_code ?? ($item->sppbDetail?->asset?->barcode ?? $item->sppbDetail?->item?->code ?? '-');
                                    $qtyKirim = (float) $item->quantity_released;
                                    $totalKirimManual += $qtyKirim;
                                    $unitName = $item->unit_name ?? $item->sppbDetail?->unit?->name ?? '-';
                                    $itemType = $item->item_type ?? 'Non Asset';

                                    $codeHtml = (! empty($codeVal) && $codeVal !== '-')
                                        ? '<br><span class="text-[11px] font-bold text-primary-600 dark:text-primary-400">Kode/Barcode: '.e($codeVal).'</span>'
                                        : '';

                                    $html .= '<tr>';
                                    $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10">'.($index + 1).'</td>';
                                    $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10"><span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium '.($itemType === 'Asset' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300').'">'.e($itemType).'</span></td>';
                                    $html .= '<td class="px-3 py-2 border-r border-gray-200 dark:border-white/10"><span class="font-medium text-gray-900 dark:text-white">'.e($itemTitle).'</span>'.$codeHtml.'</td>';
                                    $html .= '<td class="px-3 py-2 text-right font-bold text-primary-600 dark:text-primary-400 border-r border-gray-200 dark:border-white/10">'.number_format($qtyKirim, 2).'</td>';
                                    $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10">'.e($unitName).'</td>';
                                    $html .= '<td class="px-3 py-2">'.e($item->condition_on_release ?? '-').'</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '<tr class="bg-gray-50 dark:bg-white/5 font-bold">';
                                $html .= '<td colspan="3" class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">TOTAL</td>';
                                $html .= '<td class="px-3 py-2 text-right text-primary-600 dark:text-primary-400 border-r border-gray-200 dark:border-white/10">'.number_format($totalKirimManual, 2).'</td>';
                                $html .= '<td colspan="2" class="px-3 py-2"></td>';
                                $html .= '</tr></tbody></table></div>';

                                return new HtmlString($html);
                            }

                            $html = '<div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-lg">';
                            $html .= '<table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-white/10">';
                            $html .= '<thead class="bg-gray-50 dark:bg-white/5">';
                            $html .= '<tr>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10">No</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10">Jenis</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold border-r border-gray-200 dark:border-white/10">Nama Barang / Aset</th>';
                            $html .= '<th colspan="2" class="px-3 py-1.5 font-semibold text-center border-b border-r border-gray-200 dark:border-white/10">Qty SPPB</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold text-right border-r border-gray-200 dark:border-white/10">Qty Kirim Ini</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold text-right border-r border-gray-200 dark:border-white/10">Sisa SPPB</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold text-center border-r border-gray-200 dark:border-white/10">Satuan</th>';
                            $html .= '<th rowspan="2" class="px-3 py-2.5 font-semibold">Keterangan</th>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                            $html .= '<th class="px-3 py-1.5 font-semibold text-right border-r border-gray-200 dark:border-white/10">Awal</th>';
                            $html .= '<th class="px-3 py-1.5 font-semibold text-right border-r border-gray-200 dark:border-white/10">Terkirim</th>';
                            $html .= '</tr>';
                            $html .= '</thead>';
                            $html .= '<tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-gray-900">';

                            $totalAwal = 0;
                            $totalTerkirimBefore = 0;
                            $totalKirimIni = 0;
                            $totalSisa = 0;

                            foreach ($items as $index => $item) {
                                $sppbDetail = $item->sppbDetail;
                                $isAsset = ! empty($sppbDetail?->asset_id) || ! empty($sppbDetail?->asset);
                                $itemType = $isAsset ? 'Asset' : 'Non Asset';
                                $codeValue = $isAsset
                                    ? ($sppbDetail?->asset?->barcode ?? $sppbDetail?->reference_code)
                                    : ($sppbDetail?->item?->code ?? $sppbDetail?->reference_code);

                                $qtyAwal = (float) ($sppbDetail?->quantity ?? $item->quantity_requested);

                                $previouslyReleased = $sppbDetail ? (float) GoodsReleaseItem::where('sppb_detail_id', $sppbDetail->id)
                                    ->where('id', '!=', $item->id)
                                    ->whereHas('goodsRelease', function ($q) use ($record) {
                                        $q->where('status', '!=', 'CANCELLED');
                                        if ($record->id) {
                                            $q->where('id', '<', $record->id);
                                        }
                                    })
                                    ->sum('quantity_released') : 0.0;

                                $qtyKirimIni = (float) $item->quantity_released;
                                $sisaSppb = max(0.0, $qtyAwal - ($previouslyReleased + $qtyKirimIni));

                                $totalAwal += $qtyAwal;
                                $totalTerkirimBefore += $previouslyReleased;
                                $totalKirimIni += $qtyKirimIni;
                                $totalSisa += $sisaSppb;

                                $codeHtml = (! empty($codeValue) && $codeValue !== '-')
                                    ? '<br><span class="text-[11px] font-bold text-primary-600 dark:text-primary-400">'.($isAsset ? 'Barcode' : 'Kode').': '.e($codeValue).'</span>'
                                    : '';

                                $html .= '<tr>';
                                $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10">'.($index + 1).'</td>';
                                $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10"><span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium '.($isAsset ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300').'">'.e($itemType).'</span></td>';
                                $html .= '<td class="px-3 py-2 border-r border-gray-200 dark:border-white/10"><span class="font-medium text-gray-900 dark:text-white">'.e($sppbDetail?->item_asset_name).'</span>'.$codeHtml.'</td>';
                                $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($qtyAwal, 2).'</td>';
                                $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($previouslyReleased, 2).'</td>';
                                $html .= '<td class="px-3 py-2 text-right font-bold text-primary-600 dark:text-primary-400 border-r border-gray-200 dark:border-white/10">'.number_format($qtyKirimIni, 2).'</td>';
                                $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($sisaSppb, 2).'</td>';
                                $html .= '<td class="px-3 py-2 text-center border-r border-gray-200 dark:border-white/10">'.e($sppbDetail?->unit?->name).'</td>';
                                $html .= '<td class="px-3 py-2">'.e($item->condition_on_release ?? '-').'</td>';
                                $html .= '</tr>';
                            }

                            $html .= '<tr class="bg-gray-50 dark:bg-white/5 font-bold">';
                            $html .= '<td colspan="3" class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">TOTAL</td>';
                            $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($totalAwal, 2).'</td>';
                            $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($totalTerkirimBefore, 2).'</td>';
                            $html .= '<td class="px-3 py-2 text-right text-primary-600 dark:text-primary-400 border-r border-gray-200 dark:border-white/10">'.number_format($totalKirimIni, 2).'</td>';
                            $html .= '<td class="px-3 py-2 text-right border-r border-gray-200 dark:border-white/10">'.number_format($totalSisa, 2).'</td>';
                            $html .= '<td colspan="2" class="px-3 py-2"></td>';
                            $html .= '</tr>';

                            $html .= '</tbody></table></div>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),

                    Repeater::make('goodsReleaseItems')
                        ->relationship('goodsReleaseItems')
                        ->label('Barang Dikirim')
                        ->addActionLabel('Tambah Barang')
                        ->hidden(fn (string $operation) => $operation === 'view')
                        ->addable(fn ($record) => ! $record || $record->status === 'DRAFT')
                        ->deletable(fn ($record) => ! $record || $record->status === 'DRAFT')
                        ->reorderable(false)
                        ->disabled(fn (string $operation, $record) => $operation === 'edit' && $record && $record->status !== 'DRAFT')
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
                                    $alreadyReleased = (float) GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                                        ->whereHas('goodsRelease', fn ($q) => $q->where('status', '!=', 'CANCELLED'))
                                        ->sum('quantity_released');

                                    $remainingQty = max(0.0, (float) $detail->quantity - $alreadyReleased);

                                    if ($remainingQty <= 0) {
                                        continue;
                                    }

                                    $type = $detail->asset_id ? 'Asset' : 'Non Asset';
                                    $code = $detail->asset?->barcode ?? $detail->item?->code ?? $detail->reference_code ?? '-';
                                    $items[] = [
                                        'sppb_detail_id' => $detail->id,
                                        'item_type' => $type,
                                        'barcode_code' => $code,
                                        'item_name' => $detail->item_asset_name,
                                        'quantity_requested' => $remainingQty,
                                        'quantity_released' => $remainingQty,
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
                                    $items = $get('goodsReleaseItems') ?? $get('../goodsReleaseItems') ?? $get('../../goodsReleaseItems') ?? [];
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
                                    $items = $get('goodsReleaseItems') ?? $get('../goodsReleaseItems') ?? $get('../../goodsReleaseItems') ?? [];
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

                            TextInput::make('item_name')
                                ->label('Nama Barang / Aset *')
                                ->required(fn (Get $get, $record) => static::isPureManual($get, $record))
                                ->visible(fn (Get $get, $record) => static::isPureManual($get, $record))
                                ->placeholder('Ketik nama barang / aset...')
                                ->columnSpan(4),

                            Select::make('sppb_detail_id')
                                ->label('Barang SPPB')
                                ->required(fn (Get $get, $record) => ! static::isPureManual($get, $record))
                                ->hidden(fn (Get $get, $record) => static::isPureManual($get, $record))
                                ->live()
                                ->searchable()
                                ->disabled(fn ($record) => $record && $record->status !== 'DRAFT')
                                ->options(function (Get $get, $component, $record) {
                                    $livewireData = $component?->getLivewire()?->data ?? [];
                                    $sppbIds = $livewireData['sppbHeaders']
                                        ?? $get('sppbHeaders')
                                        ?? $get('../../sppbHeaders')
                                        ?? $get('../../../sppbHeaders')
                                        ?? $get('../../../../sppbHeaders')
                                        ?? [];

                                    if (empty($sppbIds) && $record) {
                                        $sppbIds = $record->sppbHeaders?->pluck('id')?->toArray() ?: ($record->sppb_header_id ? [$record->sppb_header_id] : []);
                                    }
                                    if (empty($sppbIds) && request()->query('sppb_header_id')) {
                                        $sppbIds = [(int) request()->query('sppb_header_id')];
                                    }
                                    if (empty($sppbIds)) {
                                        return [];
                                    }

                                    $details = SppbDetail::with(['unit', 'item', 'asset', 'sppbHeader'])
                                        ->whereIn('sppb_header_id', $sppbIds)
                                        ->get();

                                    $currentDetailId = $get('sppb_detail_id');
                                    $allItems = $livewireData['goodsReleaseItems'] ?? $get('goodsReleaseItems') ?? $get('../goodsReleaseItems') ?? $get('../../goodsReleaseItems') ?? [];
                                    $allSelected = collect($allItems)
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

                            Select::make('item_type')
                                ->label('Jenis')
                                ->options([
                                    'Non Asset' => 'Non Asset',
                                    'Asset' => 'Asset',
                                ])
                                ->default('Non Asset')
                                ->disabled(fn (Get $get, $record) => ! static::isPureManual($get, $record))
                                ->dehydrated()
                                ->afterStateHydrated(function (Select $component, Get $get, $record) {
                                    if (! static::isPureManual($get, $record)) {
                                        $detailId = $get('sppb_detail_id');
                                        if ($detailId) {
                                            $detail = SppbDetail::find($detailId);
                                            $component->state($detail?->asset_id ? 'Asset' : 'Non Asset');
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('barcode_code')
                                ->label('Barcode/Kode')
                                ->placeholder('Opsional')
                                ->dehydrated()
                                ->afterStateHydrated(function (TextInput $component, Get $get, $record) {
                                    if (! static::isPureManual($get, $record)) {
                                        $detailId = $get('sppb_detail_id');
                                        if ($detailId) {
                                            $detail = SppbDetail::with(['asset', 'item'])->find($detailId);
                                            $code = $detail?->asset?->barcode ?? $detail?->item?->code ?? $detail?->reference_code ?? '-';
                                            $component->state($code);
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('quantity_requested')
                                ->label('Qty SPPB Awal')
                                ->numeric()
                                ->readOnly()
                                ->hidden(fn (Get $get, $record) => static::isPureManual($get, $record))
                                ->afterStateHydrated(function (TextInput $component, Get $get) {
                                    $detailId = $get('sppb_detail_id');
                                    if ($detailId) {
                                        $detail = SppbDetail::find($detailId);
                                        if ($detail) {
                                            $component->state($detail->quantity);
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('quantity_released')
                                ->label(fn (Get $get, $record) => static::isPureManual($get, $record) ? 'Qty Kirim *' : 'Qty Kirim Ini *')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->columnSpan(1),

                            Select::make('unit_name')
                                ->label('Satuan *')
                                ->options(fn () => Unit::query()->where('is_active', true)->orderBy('name')->pluck('name', 'name')->toArray())
                                ->searchable()
                                ->required(fn (Get $get, $record) => static::isPureManual($get, $record))
                                ->disabled(fn (Get $get, $record) => ! static::isPureManual($get, $record))
                                ->dehydrated()
                                ->afterStateHydrated(function (Select $component, Get $get, $record) {
                                    if (! static::isPureManual($get, $record)) {
                                        $detailId = $get('sppb_detail_id');
                                        if ($detailId) {
                                            $detail = SppbDetail::with('unit')->find($detailId);
                                            $component->state($detail?->unit?->name);
                                        }
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('condition_on_release')
                                ->label('Keterangan')
                                ->placeholder('Keterangan / catatan barang saat rilis...')
                                ->maxLength(255)
                                ->columnSpan(3),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            // ─── PANEL 5: INFORMASI PENERIMAAN BARANG ─────────────────────────────
            Section::make('Informasi Penerimaan Barang')
                ->visible(fn ($record) => $record && (in_array($record->status, ['DELIVERED', 'RECEIVED', 'COMPLETED']) || ! empty($record->received_at)))
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])->schema([
                        TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->readOnly()
                            ->formatStateUsing(fn ($record) => $record?->recipient_name ?? $record?->receiver_name ?? '—'),

                        TextInput::make('received_at_formatted')
                            ->label('Tanggal & Waktu Diterima')
                            ->readOnly()
                            ->formatStateUsing(fn ($record) => $record?->received_at ? Carbon::parse($record->received_at)->translatedFormat('d F Y H:i:s T') : '—'),

                        TextInput::make('receiving_notes')
                            ->label('Catatan Penerimaan')
                            ->readOnly()
                            ->formatStateUsing(fn ($record) => $record?->receiving_notes ?? $record?->notes ?? '—'),
                    ]),

                    Placeholder::make('recipient_signature_preview')
                        ->label('Tanda Tangan Penerima')
                        ->content(function ($record): HtmlString {
                            if (! $record || empty($record->recipient_signature)) {
                                return new HtmlString('<p class="text-xs text-gray-500 italic">Tidak ada tanda tangan penerima.</p>');
                            }

                            $signature = $record->recipient_signature;
                            $src = str_starts_with($signature, 'data:image') || str_starts_with($signature, 'http')
                                ? $signature
                                : asset('storage/'.$signature);

                            return new HtmlString('<div class="p-2 border rounded-lg bg-gray-50 dark:bg-white/5 inline-block"><img src="'.e($src).'" alt="Tanda Tangan Penerima" class="max-h-32 object-contain" /></div>');
                        })
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
                    ->state(fn (GoodsRelease $record) => $record->is_manual ? $record->manual_release_number : $record->release_number)
                    ->description(fn (GoodsRelease $record) => $record->is_manual ? "Ref: {$record->release_number}" : null)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('release_number', 'like', "%{$search}%")
                            ->orWhere('manual_release_number', 'like', "%{$search}%");
                    }),

                TextColumn::make('jenis_surat_jalan')
                    ->label('Jenis SJ')
                    ->badge()
                    ->state(function (GoodsRelease $record): string {
                        $hasSppb = ! empty($record->sppb_header_id) || ($record->sppbHeaders?->isNotEmpty() ?? false);
                        if ($hasSppb) {
                            return $record->is_manual ? 'SPPB (Manual SJ)' : 'SPPB (Otomatis)';
                        }

                        return 'Manual (Non-SPPB)';
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'SPPB (Otomatis)' => 'info',
                            'SPPB (Manual SJ)' => 'primary',
                            'Manual (Non-SPPB)' => 'warning',
                            default => 'gray',
                        };
                    }),

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
                    ->color(function (string $state): string {
                        $enum = GoodsReleaseStatus::tryFrom($state);

                        return $enum ? $enum->color() : 'gray';
                    })
                    ->formatStateUsing(function (string $state): string {
                        $enum = GoodsReleaseStatus::tryFrom($state);

                        return $enum ? $enum->label() : $state;
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordUrl(fn (GoodsRelease $record): string => static::getUrl('view', ['record' => $record]))
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (GoodsRelease $record): bool => $record->status === 'DRAFT'),
                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (GoodsRelease $record) => route('goods-releases.preview', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (GoodsRelease $record) => $record->status !== 'DRAFT'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        /** @var GoodsRelease $record */
        if ($record->status !== 'DRAFT') {
            return false;
        }

        return parent::canEdit($record);
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

        $userRoleIds = $user->roles->pluck('id')->toArray();

        return $query->whereHas('sppbHeader', function ($sppbQuery) use ($user, $userRoleIds) {
            $sppbQuery->where(function ($q) use ($user, $userRoleIds) {
                $q->where('requester_id', $user->id)
                    ->orWhere('current_approver_id', $user->id)
                    ->orWhere(function ($sub) use ($user, $userRoleIds) {
                        $sub->whereExists(function ($rawQuery) use ($user, $userRoleIds) {
                            $rawQuery->select(DB::raw(1))
                                ->from('document_accesses')
                                ->where('document_accesses.module', 'goods_release')
                                ->where(function ($actQ) {
                                    $actQ->where('document_accesses.can_view', true)
                                        ->orWhere('document_accesses.can_create', true)
                                        ->orWhere('document_accesses.can_edit', true)
                                        ->orWhere('document_accesses.can_delete', true);
                                })
                                ->where(function ($userOrRoleQ) use ($user, $userRoleIds) {
                                    $userOrRoleQ->where('document_accesses.user_id', $user->id);
                                    if (! empty($userRoleIds)) {
                                        $userOrRoleQ->orWhereIn('document_accesses.role_id', $userRoleIds);
                                    }
                                })
                                ->where(function ($plantQ) {
                                    $plantQ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                                        ->orWhereNull('document_accesses.plant_id');
                                })
                                ->where(function ($deptQ) {
                                    $deptQ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                                        ->orWhereNull('document_accesses.department_id');
                                });
                        });
                    });

                if ($user->plant_id) {
                    $q->orWhere(function ($userPlantQ) use ($user) {
                        $userPlantQ->where('plant_id', $user->plant_id);
                        if ($user->department_id) {
                            $userPlantQ->where('department_id', $user->department_id);
                        }
                    });
                }
            });
        });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoodsReleases::route('/'),
            'create' => CreateGoodsRelease::route('/create'),
            'view' => ViewGoodsRelease::route('/{record}'),
            'edit' => EditGoodsRelease::route('/{record}/edit'),
        ];
    }
}
