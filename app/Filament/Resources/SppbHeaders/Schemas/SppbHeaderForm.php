<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Schemas;

use App\Enums\SppbStatus;
use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Models\Asset;
use App\Models\GoodsRelease;
use App\Models\Item;
use App\Models\Location;
use App\Models\SppbHeader;
use App\Models\Unit;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class SppbHeaderForm
{
    public static function configure(Schema $schema): Schema
    {
        $getRequestDateField = fn () => TextInput::make('request_date')
            ->label('Tanggal Permintaan')
            ->readOnly()
            ->prefixIcon('heroicon-m-calendar')
            ->placeholder(now()->translatedFormat('d/m/Y'))
            ->default(fn () => now()->toDateString())
            ->extraInputAttributes(['class' => 'text-center']);

        $getDateNeededField = fn () => DatePicker::make('date_needed')
            ->label('Tanggal Dibutuhkan')
            ->native(false)
            ->displayFormat('d/m/Y')
            ->prefixIcon('heroicon-m-calendar')
            ->extraInputAttributes(['class' => 'text-center'])
            ->required()
            ->default(fn () => now()->addDay()->toDateString())
            ->minDate(now()->toDateString());

        $getPlantIdField = fn () => Select::make('plant_id')
            ->label('Plant')
            ->relationship('plant', 'name', function ($query) {
                $user = auth()->user();
                if (! $user) {
                    return $query->whereRaw('1=0');
                }
                if ($user->hasRole('super_admin')) {
                    return $query;
                }

                return $query->whereIn('id', function ($sub) use ($user) {
                    $sub->select('plant_id')
                        ->from('document_accesses')
                        ->where('user_id', $user->id)
                        ->where('module', 'sppb')
                        ->where(function ($q) {
                            $q->where('can_create', true)
                                ->orWhere('can_view', true)
                                ->orWhere('can_edit', true);
                        });
                });
            })
            ->searchable()
            ->preload()
            ->required()
            ->default(fn () => auth()->user()?->plant_id)
            ->live()
            ->afterStateUpdated(function (Set $set) {
                $set('department_id', null);
                if (auth()->user()?->hasRole('super_admin')) {
                    $set('requester_id', null);
                } else {
                    $set('requester_id', auth()->id());
                }
            });

        $getDepartmentIdField = fn () => Select::make('department_id')
            ->label('Department')
            ->relationship('department', 'name', function ($query, Get $get) {
                $plantId = $get('plant_id');
                if (! $plantId) {
                    return $query->whereRaw('1=0');
                }
                $query->where('plant_id', $plantId);

                $user = auth()->user();
                if ($user && ! $user->hasRole('super_admin')) {
                    $query->whereIn('id', function ($sub) use ($user, $plantId) {
                        $sub->select('department_id')
                            ->from('document_accesses')
                            ->where('user_id', $user->id)
                            ->where('plant_id', $plantId)
                            ->where('module', 'sppb')
                            ->where(function ($q) {
                                $q->where('can_create', true)
                                    ->orWhere('can_view', true)
                                    ->orWhere('can_edit', true);
                            });
                    });
                }

                return $query;
            })
            ->searchable()
            ->preload()
            ->required()
            ->default(fn () => auth()->user()?->department_id ?? null)
            ->live()
            ->afterStateUpdated(function (Set $set) {
                if (auth()->user()?->hasRole('super_admin')) {
                    $set('requester_id', null);
                } else {
                    $set('requester_id', auth()->id());
                }
            });

        $getRequesterIdField = fn () => Select::make('requester_id')
            ->label('Pemohon')
            ->relationship('requester', 'name', function ($query, Get $get) {
                $user = auth()->user();
                if (! $user) {
                    return $query->whereRaw('1=0');
                }

                // For regular users (non super_admin), ALWAYS restrict query to auth user so default(auth()->id()) is always matched and displayed
                if (! $user->hasRole('super_admin')) {
                    return $query->where('id', $user->id);
                }

                // For Super Admin: filter dynamically based on selected Plant & Department
                $plantId = $get('plant_id');
                $deptId = $get('department_id');
                if (! $plantId || ! $deptId) {
                    return $query;
                }

                return $query->where(function ($q) use ($plantId, $deptId) {
                    $q->where('plant_id', $plantId)
                        ->where('department_id', $deptId)
                        ->orWhereIn('id', function ($sub) use ($plantId, $deptId) {
                            $sub->select('user_id')
                                ->from('document_accesses')
                                ->where('plant_id', $plantId)
                                ->where('department_id', $deptId)
                                ->where('module', 'sppb')
                                ->where(function ($accessQ) {
                                    $accessQ->where('can_create', true)
                                        ->orWhere('can_view', true)
                                        ->orWhere('can_edit', true);
                                });
                        });
                });
            })
            ->searchable(fn () => auth()->user()?->hasRole('super_admin'))
            ->preload()
            ->required()
            ->default(fn () => auth()->id())
            ->disabled(fn () => ! auth()->user()?->hasRole('super_admin'))
            ->dehydrated();

        return $schema
            ->components([
                // ─── SECTION 1: INFORMASI UTAMA SPPB ──────────────────────────────
                Section::make('Informasi Utama SPPB')
                    ->schema([
                        // Create Mode Top Row: Tgl Permintaan | Plant | Department | Pemohon (4 columns)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])->schema([
                            $getRequestDateField(),
                            $getPlantIdField(),
                            $getDepartmentIdField(),
                            $getRequesterIdField(),
                        ])
                            ->visibleOn('create'),

                        // Edit Mode Row 1: No. SPPB | Tgl Permintaan | Status (3 columns)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])->schema([
                            TextInput::make('document_number')
                                ->label('No. SPPB')
                                ->readOnly()
                                ->placeholder('Dibuat otomatis'),

                            $getRequestDateField(),

                            Placeholder::make('status_display')
                                ->label('Status')
                                ->content(function ($record): HtmlString {
                                    if (! $record) {
                                        return new HtmlString('<span class="text-sm text-gray-400 italic">—</span>');
                                    }
                                    $status = $record->status instanceof SppbStatus
                                        ? $record->status
                                        : SppbStatus::tryFrom($record->status);

                                    if (! $status) {
                                        return new HtmlString('<span class="text-sm text-gray-400 italic">—</span>');
                                    }

                                    $colorMap = [
                                        'gray' => 'bg-gray-100 text-gray-700',
                                        'warning' => 'bg-amber-100 text-amber-700',
                                        'success' => 'bg-green-100 text-green-700',
                                        'danger' => 'bg-red-100 text-red-700',
                                        'info' => 'bg-blue-100 text-blue-700',
                                    ];
                                    $class = $colorMap[$status->color()] ?? 'bg-gray-100 text-gray-700';

                                    return new HtmlString(
                                        '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$class.'">'
                                        .e($status->label())
                                        .'</span>'
                                    );
                                }),
                        ])
                            ->hiddenOn('create'),

                        // Edit Mode Row 2: Plant | Department | Pemohon (3 columns)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])->schema([
                            $getPlantIdField(),
                            $getDepartmentIdField(),
                            $getRequesterIdField(),
                        ])
                            ->hiddenOn('create'),

                        // ROW 3: Keperluan (80%) | Tanggal Dibutuhkan (20%)
                        Grid::make([
                            'default' => 1,
                            'lg' => 10,
                        ])->schema([
                            TextInput::make('needed_name')
                                ->label('Keperluan')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Isi keperluan permintaan...')
                                ->columnSpan(8),

                            $getDateNeededField()
                                ->columnSpan(2),
                        ]),

                        // ROW 4: Lokasi Asal | Lokasi Tujuan
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])->schema([
                            Select::make('origin_location_id')
                                ->label('Lokasi Asal')
                                ->relationship('originLocation', 'name', fn ($query, $get) => $query->when($get('destination_location_id'), fn ($q, $destId) => $q->where('id', '!=', $destId)))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                    $set('origin_address_display', static::getLocationAddress($state));
                                    if ($state && $state === (int) $get('destination_location_id')) {
                                        $set('destination_location_id', null);
                                        $set('destination_address_display', null);
                                    }
                                }),

                            Select::make('destination_location_id')
                                ->label('Lokasi Tujuan')
                                ->relationship('destinationLocation', 'name', fn ($query, $get) => $query->when($get('origin_location_id'), fn ($q, $originId) => $q->where('id', '!=', $originId)))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                    $set('destination_address_display', static::getLocationAddress($state));
                                    if ($state && $state === (int) $get('origin_location_id')) {
                                        $set('origin_location_id', null);
                                        $set('origin_address_display', null);
                                    }
                                }),
                        ]),

                        // ROW 5: Alamat Asal | Alamat Tujuan (readonly)
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])->schema([
                            Textarea::make('origin_address_display')
                                ->label('Alamat Asal')
                                ->readOnly()
                                ->rows(3)
                                ->placeholder('Alamat akan terisi otomatis setelah lokasi asal dipilih')
                                ->default(function (Get $get): string {
                                    return static::getLocationAddress($get('origin_location_id'));
                                })
                                ->dehydrated(false),

                            Textarea::make('destination_address_display')
                                ->label('Alamat Tujuan')
                                ->readOnly()
                                ->rows(3)
                                ->placeholder('Alamat akan terisi otomatis setelah lokasi tujuan dipilih')
                                ->default(function (Get $get): string {
                                    return static::getLocationAddress($get('destination_location_id'));
                                })
                                ->dehydrated(false),
                        ]),

                        // ROW 6: Keterangan (Full Width, visible on Create & Edit)
                        Textarea::make('purpose')
                            ->label('Keterangan')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Isi keterangan tambahan atau instruksi khusus (opsional)...')
                            ->columnSpanFull(),

                        // ROW 7: Lampiran Saat Ini (Edit Mode Only)
                        Placeholder::make('existing_attachments')
                            ->label('Lampiran Saat Ini')
                            ->content(function ($record): HtmlString {
                                if (! $record || $record->attachments->isEmpty()) {
                                    return new HtmlString('<p class="text-xs text-gray-500 italic">Belum ada lampiran</p>');
                                }

                                $html = '<ul class="divide-y divide-gray-200 dark:divide-white/5 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-gray-900 shadow-sm overflow-hidden">';
                                foreach ($record->attachments as $attachment) {
                                    $previewUrl = URL::signedRoute('attachments.viewer', ['attachment' => $attachment->uuid]);
                                    $downloadUrl = URL::signedRoute('attachments.download', ['attachment' => $attachment->uuid]);
                                    $deleteUrl = URL::signedRoute('attachments.delete', ['attachment' => $attachment->uuid]);

                                    $html .= '<li class="flex items-center justify-between p-2 hover:bg-gray-50 dark:hover:bg-white/5">';
                                    $html .= '<div class="flex items-center space-x-2 min-w-0 flex-1 mr-2">';
                                    $html .= '<span class="text-xs text-gray-700 dark:text-gray-300 truncate font-medium" title="'.e($attachment->original_name).'">'.e($attachment->original_name).'</span>';
                                    $html .= '</div>';
                                    $html .= '<div class="flex items-center space-x-1.5 flex-shrink-0">';
                                    $html .= '<a href="'.$previewUrl.'" target="_blank" class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/30 px-1.5 py-0.5 rounded hover:bg-primary-100 transition-colors">Preview</a>';
                                    $html .= '<a href="'.$downloadUrl.'" class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-1.5 py-0.5 rounded hover:bg-gray-100 transition-colors">Download</a>';
                                    $html .= '<a href="'.$deleteUrl.'" onclick="return confirm(\'Hapus lampiran ini?\')" class="text-[10px] font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30 px-1.5 py-0.5 rounded hover:bg-red-100 transition-colors">Hapus</a>';
                                    $html .= '</div>';
                                    $html .= '</li>';
                                }
                                $html .= '</ul>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull()
                            ->hiddenOn('create'),

                        // ROW 8: Upload Lampiran (Create & Edit Mode)
                        FileUpload::make('uploaded_attachments')
                            ->label('Upload Lampiran (File / Gambar / PDF)')
                            ->multiple()
                            ->directory('sppb-attachments')
                            ->preserveFilenames()
                            ->downloadable()
                            ->previewable(true)
                            ->reorderable(true)
                            ->appendFiles()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 2: DETAIL BARANG / ASSET ────────────────────────
                Section::make('Detail Barang / Asset')
                    ->schema([
                        // Header Grid (Hanya tampil di Desktop)
                        Grid::make([
                            'default' => 1,
                            'sm' => 4,
                            'lg' => 16,
                        ])
                            ->schema([
                                Placeholder::make('hdr_jenis')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Jenis</span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_barcode_kode')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Barcode/Kode <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(2),

                                Placeholder::make('hdr_nama_aset_barang')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Nama Aset/Barang <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(6),

                                Placeholder::make('hdr_qty')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Qty <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(1),

                                Placeholder::make('hdr_satuan')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Satuan <span class="text-red-600 dark:text-red-400">*</span></span>'))
                                    ->columnSpan(2),

                                Placeholder::make('hdr_remarks')
                                    ->hiddenLabel()
                                    ->content(new HtmlString('<span class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400">Keterangan / Spesifikasi</span>'))
                                    ->columnSpan(4),
                            ])
                            ->extraAttributes(['class' => 'hidden lg:grid mb-2']),

                        Repeater::make('sppbDetails')
                            ->relationship('sppbDetails')
                            ->hiddenLabel()
                            ->addActionLabel('Tambah Item')
                            ->schema([
                                // Asset / Non Asset toggle
                                Checkbox::make('barcode_confirmed')
                                    ->label('Jenis')
                                    ->default(false)
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('item_id', null);
                                        $set('asset_id', null);
                                        $set('reference_code', null);
                                        $set('item_asset_name', null);
                                        $set('unit_id', null);
                                        if ($state) {
                                            $set('quantity', 1);
                                        }
                                    })
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(1),

                                Hidden::make('item_id'),

                                Hidden::make('reference_code'),

                                Select::make('asset_id')
                                    ->label('Barcode/Kode')
                                    ->relationship('asset', 'barcode')
                                    ->searchable(['barcode'])
                                    ->required(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?int $state) {
                                        if (! $state) {
                                            $set('reference_code', null);
                                            $set('item_asset_name', null);
                                            $set('unit_id', null);

                                            return;
                                        }
                                        $asset = Asset::find($state);
                                        if ($asset) {
                                            $set('reference_code', $asset->barcode);
                                            $set('item_asset_name', $asset->asset_name);
                                            $set('unit_id', $asset->unit_id);
                                            $set('quantity', 1);
                                        }
                                    })
                                    ->visible(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(2),

                                TextInput::make('item_asset_name')
                                    ->label('Nama Aset/Barang')
                                    ->placeholder(fn (Get $get) => $get('barcode_confirmed') ? 'Nama aset terisi otomatis' : 'Ketik nama barang di sini...')
                                    ->required()
                                    ->readOnly(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->maxLength(1000)
                                    ->datalist(fn (Get $get) => $get('barcode_confirmed') ? [] : Item::where('is_active', true)->pluck('name')->toArray())
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                        if ($get('barcode_confirmed')) {
                                            return;
                                        }

                                        if (empty($state)) {
                                            $set('item_id', null);
                                            $set('reference_code', null);
                                            $set('unit_id', null);

                                            return;
                                        }

                                        $item = Item::where('name', $state)->where('is_active', true)->first();
                                        if ($item) {
                                            $set('item_id', $item->id);
                                            $set('reference_code', $item->code);
                                            $set('unit_id', $item->unit_id);
                                        } else {
                                            $set('item_id', null);
                                            $set('reference_code', null);
                                        }
                                    })
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(fn (Get $get) => $get('barcode_confirmed') ? 6 : 8),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->required()
                                    ->readOnly(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(1),

                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->options(fn () => Unit::getGroupedOptions())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->dehydrated()
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(2),

                                Textarea::make('remarks')
                                    ->label('Keterangan / Spesifikasi')
                                    ->rows(2)
                                    ->maxLength(65535)
                                    ->extraFieldWrapperAttributes(['class' => 'lg:[&_label]:hidden'])
                                    ->columnSpan(4),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 4,
                                'lg' => 16,
                            ])
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 3: DAFTAR SURAT JALAN TERKAIT ───────────────────
                Section::make('Daftar Surat Jalan Terkait')
                    ->schema([
                        Placeholder::make('goods_release_list_form')
                            ->hiddenLabel()
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                return static::renderGoodsReleaseList($record);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->hiddenOn('create')
                    ->visible(fn ($record) => $record && (
                        in_array($record?->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED']) ||
                        $record->goodsReleases()->exists() ||
                        $record->goodsReleasesPivot()->exists()
                    )),

                // ─── SECTION 4: WORKFLOW APPROVAL ─────────────────────────────
                Section::make('Workflow Persetujuan')
                    ->schema([
                        Placeholder::make('workflow_timeline')
                            ->hiddenLabel()
                            ->content(function ($record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                return static::renderWorkflowTimeline($record);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->hiddenOn('create'),
            ]);
    }

    /**
     * Get formatted address for a location.
     */
    protected static function getLocationAddress(?int $locationId): string
    {
        if (! $locationId) {
            return '';
        }

        $location = Location::find($locationId);

        if (! $location) {
            return '';
        }

        return trim($location->address ?? '');
    }

    /**
     * Render enterprise horizontal workflow list table as HTML.
     */
    public static function renderWorkflowTimeline(SppbHeader $record): HtmlString
    {
        $logs = $record->sppbStatusLogs()
            ->with(['actor.positions.position', 'workflowInstanceStep'])
            ->orderBy('logged_at', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return new HtmlString(
                '<p class="text-sm text-gray-400 italic py-4 text-center">'
                .'Belum ada riwayat aktivitas workflow.'
                .'</p>'
            );
        }

        $filteredLogs = $logs->filter(fn ($log) => $log->action !== 'WORKFLOW_GENERATED');

        $html = '<div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-gray-900 shadow-sm overflow-hidden">';
        $html .= '<div class="fi-ta-content overflow-x-auto">';
        $html .= '<table style="width: 100%; table-layout: fixed;" class="w-full divide-y divide-gray-200 dark:divide-white/5 text-left text-sm">';
        $html .= '<thead class="bg-gray-50 dark:bg-white/5">';
        $html .= '<tr>';
        $html .= '<th scope="col" style="width: 25%;" class="px-8 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengguna</th>';
        $html .= '<th scope="col" style="width: 25%;" class="px-8 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tindakan</th>';
        $html .= '<th scope="col" style="width: 35%;" class="px-8 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Catatan</th>';
        $html .= '<th scope="col" style="width: 15%;" class="px-8 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Waktu Tindakan</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody class="divide-y divide-gray-200 dark:divide-white/5">';

        foreach ($filteredLogs as $log) {
            $user = $log->actor;
            $userLabel = '—';
            $posName = '';

            if ($user) {
                $nik = $user->nik ? e($user->nik) : '—';
                $name = $user->name ? e($user->name) : '—';
                $userLabel = "{$nik} - {$name}";

                // Fetch position
                if ($user->relationLoaded('positions')) {
                    $activePositions = $user->positions->filter(fn ($p) => $p->is_active);
                    $primaryPos = $activePositions->firstWhere('is_primary', true);
                    $anyPos = $primaryPos ?? $activePositions->first();

                    if ($anyPos && $anyPos->position) {
                        $posName = $anyPos->position->name;
                    }
                }

                if (empty($posName)) {
                    // Fallback to roles
                    $posName = $user->roles->pluck('name')
                        ->map(fn ($r) => str_replace('_', ' ', ucwords($r, '_')))
                        ->first() ?? '';
                }
            }

            $posSuffix = $posName ? " {$posName}" : '';

            $stepCode = $log->workflowInstanceStep?->code ?? '';
            $stepName = $log->workflowInstanceStep?->name ?? '';
            $isBat = str_contains(strtoupper($stepCode), 'BAT') || str_contains(strtoupper($stepName), 'BAT');

            // Format Tindakan based on action and position
            $actionLabel = match ($log->action) {
                'SUBMIT_QUEUED' => 'Diajukan'.$posSuffix,
                'STEP_APPROVED' => $isBat ? 'Diverifikasi BAT' : 'Disetujui'.$posSuffix,
                'SPPB_APPROVED' => 'Disetujui Penuh',
                'REVISION_REQUESTED' => 'Revisi Diminta oleh'.$posSuffix,
                'SPPB_REJECTED' => 'Ditolak oleh'.$posSuffix,
                'SPPB_CANCELLED' => 'Dibatalkan oleh'.$posSuffix,
                'BAT_OPENED' => 'Proses Verifikasi BAT',
                'GOODS_RELEASE_DELIVERED', 'GOODS_RELEASE_RECEIVED' => 'Surat Jalan Diterima'.$posSuffix,
                'GOODS_RELEASE_CREATED' => 'Surat Jalan Dibuat'.$posSuffix,
                'GOODS_RELEASE_CANCELLED' => 'Surat Jalan Dibatalkan'.$posSuffix,
                default => str_replace('_', ' ', ucwords(strtolower((string) $log->action), '_')).$posSuffix,
            };

            // Formatting colors for action labels matching Filament native badges
            $badgeColor = match ($log->action) {
                'SUBMIT_QUEUED' => 'text-blue-700 bg-blue-500/10 ring-blue-600/10 dark:text-blue-400 dark:bg-blue-500/20 dark:ring-blue-500/30',
                'STEP_APPROVED' => $isBat ? 'text-green-700 bg-green-500/10 ring-green-600/10 dark:text-green-400 dark:bg-green-500/20 dark:ring-green-500/30' : 'text-green-700 bg-green-500/10 ring-green-600/10 dark:text-green-400 dark:bg-green-500/20 dark:ring-green-500/30',
                'SPPB_APPROVED' => 'text-emerald-700 bg-emerald-500/10 ring-emerald-600/10 dark:text-emerald-400 dark:bg-emerald-500/20 dark:ring-emerald-500/30',
                'BAT_OPENED' => 'text-cyan-700 bg-cyan-500/10 ring-cyan-600/10 dark:text-cyan-400 dark:bg-cyan-500/20 dark:ring-cyan-500/30',
                'GOODS_RELEASE_DELIVERED', 'GOODS_RELEASE_RECEIVED' => 'text-teal-700 bg-teal-500/10 ring-teal-600/10 dark:text-teal-400 dark:bg-teal-500/20 dark:ring-teal-500/30',
                'GOODS_RELEASE_CREATED' => 'text-indigo-700 bg-indigo-500/10 ring-indigo-600/10 dark:text-indigo-400 dark:bg-indigo-500/20 dark:ring-indigo-500/30',
                'REVISION_REQUESTED' => 'text-amber-700 bg-amber-500/10 ring-amber-600/10 dark:text-amber-400 dark:bg-amber-500/20 dark:ring-amber-500/30',
                'SPPB_REJECTED' => 'text-red-700 bg-red-500/10 ring-red-600/10 dark:text-red-400 dark:bg-red-500/20 dark:ring-red-500/30',
                'SPPB_CANCELLED' => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30',
                default => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30'
            };

            $actionBadge = '<span class="fi-badge inline-flex items-center justify-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$badgeColor.'">'.$actionLabel.'</span>';

            $remarks = $log->remarks ? e($log->remarks) : '—';
            $timeFormatted = $log->logged_at ? Carbon::parse($log->logged_at)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y H:i') : '—';

            $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-white/5">';
            $html .= '<td class="px-8 py-4 text-sm text-gray-950 dark:text-white font-medium">'.$userLabel.'</td>';
            $html .= '<td class="px-8 py-4">'.$actionBadge.'</td>';
            $html .= '<td class="px-8 py-4 text-sm text-gray-600 dark:text-gray-300 break-words" title="'.($log->remarks ? e($log->remarks) : '').'">'.$remarks.'</td>';
            $html .= '<td class="px-8 py-4 text-sm text-gray-500 dark:text-gray-400">'.$timeFormatted.'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';

        return new HtmlString($html);
    }

    /**
     * Render list of related Surat Jalan (Goods Release) as HTML table.
     */
    public static function renderGoodsReleaseList(SppbHeader $record): HtmlString
    {
        $directReleases = GoodsRelease::where('sppb_header_id', $record->id)->get();
        $pivotReleases = $record->goodsReleasesPivot;

        $releases = $directReleases->merge($pivotReleases)
            ->unique('id')
            ->sortByDesc('created_at');

        if ($releases->isEmpty()) {
            return new HtmlString(
                '<div class="rounded-xl border border-dashed border-gray-300 dark:border-white/10 p-6 text-center text-sm text-gray-500 italic dark:text-gray-400">'
                .'Belum ada Surat Jalan yang dibuat untuk dokumen SPPB ini.'
                .'</div>'
            );
        }

        $html = '<div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-gray-900 shadow-sm overflow-hidden">';
        $html .= '<div class="fi-ta-content overflow-x-auto">';
        $html .= '<table style="width: 100%; table-layout: fixed;" class="w-full divide-y divide-gray-200 dark:divide-white/5 text-left text-sm">';
        $html .= '<thead class="bg-gray-50 dark:bg-white/5">';
        $html .= '<tr>';
        $html .= '<th scope="col" style="width: 18%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">No. Surat Jalan</th>';
        $html .= '<th scope="col" style="width: 12%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tgl Pengiriman</th>';
        $html .= '<th scope="col" style="width: 12%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Status</th>';
        $html .= '<th scope="col" style="width: 15%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengirim</th>';
        $html .= '<th scope="col" style="width: 15%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Penerima</th>';
        $html .= '<th scope="col" style="width: 16%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengemudi / Ekspedisi</th>';
        $html .= '<th scope="col" style="width: 12%;" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right dark:text-gray-400">Aksi</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody class="divide-y divide-gray-200 dark:divide-white/5">';

        foreach ($releases as $release) {
            $release->loadMissing(['createdBy', 'senderUser', 'receiverUser', 'receivedBy']);

            $noSj = $release->is_manual && $release->manual_release_number
                ? e($release->manual_release_number)
                : e($release->release_number);

            $deliveryDate = $release->delivery_date
                ? Carbon::parse($release->delivery_date)->format('d/m/Y')
                : '—';

            $statusVal = strtoupper((string) ($release->status ?? 'DRAFT'));
            $statusLabel = match ($statusVal) {
                'DRAFT' => 'Draft',
                'RELEASED', 'IN_TRANSIT', 'PENDING' => 'Dikirim',
                'DELIVERED', 'RECEIVED', 'COMPLETED' => 'Diterima',
                'CANCELLED' => 'Dibatalkan',
                default => $statusVal,
            };

            $badgeColor = match ($statusVal) {
                'DRAFT' => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30',
                'RELEASED', 'IN_TRANSIT', 'PENDING' => 'text-blue-700 bg-blue-500/10 ring-blue-600/10 dark:text-blue-400 dark:bg-blue-500/20 dark:ring-blue-500/30',
                'DELIVERED', 'RECEIVED', 'COMPLETED' => 'text-emerald-700 bg-emerald-500/10 ring-emerald-600/10 dark:text-emerald-400 dark:bg-emerald-500/20 dark:ring-emerald-500/30',
                'CANCELLED' => 'text-red-700 bg-red-500/10 ring-red-600/10 dark:text-red-400 dark:bg-red-500/20 dark:ring-red-500/30',
                default => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30',
            };

            $statusBadge = '<span class="fi-badge inline-flex items-center justify-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$badgeColor.'">'.$statusLabel.'</span>';

            $sender = e($release->createdBy?->name ?? $release->senderUser?->name ?? $release->sender_name ?? '—');
            $receiver = e($release->recipient_name ?? $release->receivedBy?->name ?? $release->receiverUser?->name ?? $release->receiver_name ?? '—');

            $driverInfo = e($release->driver_name ?? '—');
            if ($release->vehicle_number) {
                $driverInfo .= ' ('.e($release->vehicle_number).')';
            }
            if ($release->expedition_name) {
                $driverInfo .= '<br/><span class="text-xs text-gray-500 dark:text-gray-400">Exp: '.e($release->expedition_name).'</span>';
            }

            $viewUrl = GoodsReleaseResource::getUrl('view', ['record' => $release]);
            $previewUrl = route('goods-releases.preview', $release);

            $actionsHtml = '<div class="flex items-center justify-end gap-2">';
            $actionsHtml .= '<a href="'.e($viewUrl).'" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium hover:underline text-xs" title="Lihat Detail Surat Jalan">Detail</a>';
            if ($statusVal !== 'DRAFT') {
                $actionsHtml .= '<span class="text-gray-300 dark:text-gray-700">|</span>';
                $actionsHtml .= '<a href="'.e($previewUrl).'" target="_blank" class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium hover:underline text-xs" title="Cetak / Preview PDF">Cetak</a>';
            }
            $actionsHtml .= '</div>';

            $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-white/5">';
            $html .= '<td class="px-6 py-4 font-mono font-semibold text-primary-600 dark:text-primary-400 text-xs">'.$noSj.'</td>';
            $html .= '<td class="px-6 py-4 text-xs text-gray-700 dark:text-gray-300">'.$deliveryDate.'</td>';
            $html .= '<td class="px-6 py-4">'.$statusBadge.'</td>';
            $html .= '<td class="px-6 py-4 text-xs text-gray-900 dark:text-white">'.$sender.'</td>';
            $html .= '<td class="px-6 py-4 text-xs text-gray-900 dark:text-white">'.$receiver.'</td>';
            $html .= '<td class="px-6 py-4 text-xs text-gray-700 dark:text-gray-300">'.$driverInfo.'</td>';
            $html .= '<td class="px-6 py-4 text-right">'.$actionsHtml.'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';

        return new HtmlString($html);
    }
}
