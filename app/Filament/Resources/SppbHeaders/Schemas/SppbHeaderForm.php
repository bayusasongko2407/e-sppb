<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Schemas;

use App\Enums\SppbStatus;
use App\Models\Asset;
use App\Models\Item;
use App\Models\Location;
use App\Models\SppbHeader;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class SppbHeaderForm
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
                            // No. SPPB — readonly, hidden on create
                            TextInput::make('document_number')
                                ->label('No. SPPB')
                                ->readOnly()
                                ->placeholder('Dibuat otomatis')
                                ->hiddenOn('create'),

                            // Tanggal Permintaan — readonly
                            TextInput::make('request_date')
                                ->label('Tanggal Permintaan')
                                ->readOnly()
                                ->placeholder(now()->translatedFormat('d/m/Y'))
                                ->default(fn () => now()->toDateString()),

                            // Status — readonly badge via Placeholder, hidden on create
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
                                })
                                ->hiddenOn('create'),

                            // Plant — editable
                            Select::make('plant_id')
                                ->label('Plant')
                                ->relationship('plant', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn () => auth()->user()?->plant_id)
                                ->live(),

                            // Department — editable
                            Select::make('department_id')
                                ->label('Department')
                                ->relationship('department', 'name', fn ($query, $get) => $query->when($get('plant_id'), fn ($q, $plantId) => $q->where('plant_id', $plantId)))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn () => auth()->user()?->department_id ?? null),

                            // Requester — editable
                            Select::make('requester_id')
                                ->label('Requester')
                                ->relationship('requester', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn () => auth()->id()),
                        ]),

                        // ROW 2: Lokasi Asal | Lokasi Tujuan | Keperluan (span rest)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 6,
                        ])->schema([
                            // Lokasi Asal
                            Select::make('origin_location_id')
                                ->label('Lokasi Asal')
                                ->relationship('originLocation', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set, ?int $state) => $set(
                                    'origin_address_display',
                                    static::getLocationAddress($state)
                                ))
                                ->columnSpan(1),

                            // Lokasi Tujuan
                            Select::make('destination_location_id')
                                ->label('Lokasi Tujuan')
                                ->relationship('destinationLocation', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set, ?int $state) => $set(
                                    'destination_address_display',
                                    static::getLocationAddress($state)
                                ))
                                ->columnSpan(1),

                            // Keperluan — manual text, spans remaining 4 columns
                            TextInput::make('needed_name')
                                ->label('Keperluan')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Isi keperluan permintaan...')
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ]),
                        ]),

                        // ROW 3: Alamat Asal | Alamat Tujuan (readonly, multiline)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 2,
                        ])->schema([
                            Textarea::make('origin_address_display')
                                ->label('Alamat')
                                ->readOnly()
                                ->rows(3)
                                ->placeholder('Alamat akan terisi otomatis setelah lokasi asal dipilih')
                                ->default(function (Get $get): string {
                                    return static::getLocationAddress($get('origin_location_id'));
                                })
                                ->dehydrated(false),

                            Textarea::make('destination_address_display')
                                ->label('Alamat')
                                ->readOnly()
                                ->rows(3)
                                ->placeholder('Alamat akan terisi otomatis setelah lokasi tujuan dipilih')
                                ->default(function (Get $get): string {
                                    return static::getLocationAddress($get('destination_location_id'));
                                })
                                ->dehydrated(false),
                        ]),

                        // ROW 4: Tanggal Kebutuhan | Keterangan (textarea, span rest)
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 6,
                        ])->schema([
                            DatePicker::make('date_needed')
                                ->label('Tanggal Kebutuhan')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'lg' => 1,
                                ]),

                            Textarea::make('purpose')
                                ->label('Keterangan')
                                ->rows(3)
                                ->maxLength(65535)
                                ->placeholder('Keterangan tambahan permintaan...')
                                ->columnSpan([
                                    'default' => 1,
                                    'sm' => 1,
                                    'lg' => 5,
                                ]),
                        ]),

                        // ROW 5: Lampiran — full width
                        FileUpload::make('attachments')
                            ->label('Lampiran')
                            ->multiple()
                            ->directory('sppb-attachments')
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
                        Repeater::make('sppbDetails')
                            ->relationship('sppbDetails')
                            ->label('')
                            ->addActionLabel('Tambah Item')
                            ->schema([
                                // Asset / Non Asset toggle
                                ToggleButtons::make('barcode_confirmed')
                                    ->label('Jenis')
                                    ->options([
                                        0 => 'Non Asset',
                                        1 => 'Asset',
                                    ])
                                    ->default(0)
                                    ->inline()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $state) {
                                        if ($state === null) {
                                            $component->state(0);
                                        } else {
                                            $component->state($state ? 1 : 0);
                                        }
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state == 1) {
                                            $set('quantity', 1);
                                        }
                                    })
                                    ->columnSpan(1),

                                // Kode — readonly, auto-filled
                                TextInput::make('reference_code')
                                    ->label('Kode')
                                    ->readOnly()
                                    ->placeholder('Otomatis')
                                    ->columnSpan(1),

                                // Nama Barang / Asset — depends on toggle
                                Select::make('item_id')
                                    ->label('Nama Barang')
                                    ->relationship('item', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?int $state): void {
                                        if (! $state) {
                                            $set('reference_code', null);
                                            $set('unit_id', null);
                                            $set('remarks', null);

                                            return;
                                        }
                                        $item = Item::with('unit')->find($state);
                                        if ($item) {
                                            $set('reference_code', $item->code);
                                            $set('unit_id', $item->unit_id);
                                            $set('remarks', $item->specification ?? null);
                                        }
                                    })
                                    ->visible(fn (Get $get): bool => $get('barcode_confirmed') != 1)
                                    ->columnSpan(2),

                                Select::make('asset_id')
                                    ->label('Nama Asset')
                                    ->relationship('asset', 'asset_name')
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?int $state): void {
                                        if (! $state) {
                                            $set('reference_code', null);
                                            $set('unit_id', null);

                                            return;
                                        }
                                        $asset = Asset::find($state);
                                        if ($asset) {
                                            $set('reference_code', $asset->barcode ?? null);
                                            $set('unit_id', $asset->unit_id ?? null);
                                            $set('quantity', 1);
                                        }
                                    })
                                    ->visible(fn (Get $get): bool => $get('barcode_confirmed') == 1)
                                    ->columnSpan(2),

                                // Qty
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->required()
                                    ->readOnly(fn (Get $get): bool => $get('barcode_confirmed') == 1)
                                    ->columnSpan(1),

                                // Satuan — readonly, auto-filled
                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->relationship('unit', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                // Keterangan / Spesifikasi — editable
                                Textarea::make('remarks')
                                    ->label('Keterangan / Spesifikasi')
                                    ->rows(2)
                                    ->maxLength(65535)
                                    ->columnSpan(2),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 4,
                                'lg' => 8,
                            ])
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── SECTION 3: WORKFLOW APPROVAL ─────────────────────────────
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

            $posSuffix = $posName ? " {{$posName}}" : '';

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
                'BAT_OPENED' => 'Proses Verifikasi BAT'.$posSuffix,
                default => e($log->action)
            };

            // Formatting colors for action labels matching Filament native badges
            $badgeColor = match ($log->action) {
                'SUBMIT_QUEUED' => 'text-blue-700 bg-blue-500/10 ring-blue-600/10 dark:text-blue-400 dark:bg-blue-500/20 dark:ring-blue-500/30',
                'STEP_APPROVED' => $isBat ? 'text-green-700 bg-green-500/10 ring-green-600/10 dark:text-green-400 dark:bg-green-500/20 dark:ring-green-500/30' : 'text-green-700 bg-green-500/10 ring-green-600/10 dark:text-green-400 dark:bg-green-500/20 dark:ring-green-500/30',
                'SPPB_APPROVED' => 'text-emerald-700 bg-emerald-500/10 ring-emerald-600/10 dark:text-emerald-400 dark:bg-emerald-500/20 dark:ring-emerald-500/30',
                'BAT_OPENED' => 'text-cyan-700 bg-cyan-500/10 ring-cyan-600/10 dark:text-cyan-400 dark:bg-cyan-500/20 dark:ring-cyan-500/30',
                'REVISION_REQUESTED' => 'text-amber-700 bg-amber-500/10 ring-amber-600/10 dark:text-amber-400 dark:bg-amber-500/20 dark:ring-amber-500/30',
                'SPPB_REJECTED' => 'text-red-700 bg-red-500/10 ring-red-600/10 dark:text-red-400 dark:bg-red-500/20 dark:ring-red-500/30',
                'SPPB_CANCELLED' => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30',
                default => 'text-gray-700 bg-gray-500/10 ring-gray-600/10 dark:text-gray-400 dark:bg-gray-500/20 dark:ring-gray-500/30'
            };

            $actionBadge = '<span class="fi-badge inline-flex items-center justify-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$badgeColor.'">'.$actionLabel.'</span>';

            $remarks = $log->remarks ? e($log->remarks) : '—';
            $timeFormatted = $log->logged_at ? Carbon::parse($log->logged_at)->translatedFormat('d M Y H:i') : '—';

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
}
