<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Schemas;

use App\Enums\SppbStatus;
use App\Models\Asset;
use App\Models\Item;
use App\Models\Location;
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
use Illuminate\Database\Eloquent\Collection;
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
                                ->default(fn () => auth()->user()?->plant_id),

                            // Department — editable
                            Select::make('department_id')
                                ->label('Department')
                                ->relationship('department', 'name')
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
                                        false => 'Non Asset',
                                        true => 'Asset',
                                    ])
                                    ->default(false)
                                    ->inline()
                                    ->live()
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
                                    ->visible(fn (Get $get): bool => ! $get('barcode_confirmed'))
                                    ->columnSpan(2),

                                Select::make('asset_id')
                                    ->label('Nama Asset')
                                    ->relationship('asset', 'asset_location_name')
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
                                        }
                                    })
                                    ->visible(fn (Get $get): bool => (bool) $get('barcode_confirmed'))
                                    ->columnSpan(2),

                                // Qty
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->required()
                                    ->columnSpan(1),

                                // Satuan — readonly, auto-filled
                                Select::make('unit_id')
                                    ->label('Satuan')
                                    ->relationship('unit', 'name')
                                    ->disabled()
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
                            ->label('')
                            ->content(function ($record): HtmlString {
                                if (! $record || ! $record->currentWorkflowInstance) {
                                    return new HtmlString(
                                        '<p class="text-sm text-gray-400 italic py-4 text-center">'
                                        .'Workflow persetujuan akan tampil setelah dokumen diajukan.'
                                        .'</p>'
                                    );
                                }

                                $steps = $record->currentWorkflowInstance
                                    ->workflowInstanceSteps()
                                    ->with('actedBy')
                                    ->orderBy('sequence')
                                    ->get();

                                if ($steps->isEmpty()) {
                                    return new HtmlString(
                                        '<p class="text-sm text-gray-400 italic py-4 text-center">'
                                        .'Belum ada langkah workflow.'
                                        .'</p>'
                                    );
                                }

                                return static::renderWorkflowTimeline($steps);
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
     * Render enterprise horizontal workflow timeline as HTML.
     *
     * @param  Collection  $steps
     */
    public static function renderWorkflowTimeline($steps): HtmlString
    {
        $html = '<div class="overflow-x-auto pb-2">';
        $html .= '<div class="flex items-start gap-0 min-w-max">';

        foreach ($steps as $index => $step) {
            $status = $step->status;

            [$borderColor, $bgColor, $textColor, $badgeBg, $badgeText, $statusLabel] = match ($status) {
                'APPROVED' => ['border-green-400',  'bg-green-50',  'text-green-800',  'bg-green-100',  'text-green-700',  'Selesai'],
                'PENDING' => ['border-blue-400',   'bg-blue-50',   'text-blue-800',   'bg-blue-100',   'text-blue-700',   'Berjalan'],
                'QUEUED' => ['border-gray-300',   'bg-gray-50',   'text-gray-600',   'bg-gray-100',   'text-gray-500',   'Menunggu'],
                'REJECTED',
                'REVISION_REQUESTED' => ['border-red-400',    'bg-red-50',    'text-red-800',    'bg-red-100',    'text-red-700',    'Ditolak'],
                'CANCELLED' => ['border-gray-400',   'bg-gray-100',  'text-gray-500',   'bg-gray-200',   'text-gray-500',   'Dibatalkan'],
                'EXPIRED' => ['border-orange-400', 'bg-orange-50', 'text-orange-800', 'bg-orange-100', 'text-orange-700', 'Kedaluwarsa'],
                default => ['border-gray-300',   'bg-gray-50',   'text-gray-600',   'bg-gray-100',   'text-gray-500',   $status],
            };

            $actedByName = $step->actedBy?->name ?? '—';
            $dueAt = $step->due_at ? Carbon::parse($step->due_at)->translatedFormat('d M Y') : '—';
            $acteAt = $step->acted_at ? Carbon::parse($step->acted_at)->translatedFormat('d M Y') : null;

            // Card
            $html .= '<div class="flex flex-col items-center">';

            // Step card
            $html .= '<div class="w-52 rounded-xl border-2 '.$borderColor.' '.$bgColor.' p-3 shadow-sm">';

            // Step header
            $html .= '<div class="flex items-center justify-between mb-2">';
            $html .= '<span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Langkah '.e($step->sequence).'</span>';
            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$badgeBg.' '.$badgeText.'">'.e($statusLabel).'</span>';
            $html .= '</div>';

            // Step name
            $html .= '<p class="text-sm font-semibold '.$textColor.' mb-1 truncate" title="'.e($step->name).'">'.e($step->name).'</p>';

            // Approver
            $html .= '<p class="text-xs text-gray-500 truncate" title="'.e($actedByName).'">';
            $html .= '<span class="font-medium">Approver:</span> '.e($actedByName);
            $html .= '</p>';

            // Deadline
            $html .= '<p class="text-xs text-gray-400 mt-1">';
            $html .= '<span class="font-medium">Deadline:</span> '.e($dueAt);
            $html .= '</p>';

            // Tanggal aksi (jika sudah diaksi)
            if ($acteAt) {
                $html .= '<p class="text-xs text-gray-400">';
                $html .= '<span class="font-medium">Diproses:</span> '.e($acteAt);
                $html .= '</p>';
            }

            $html .= '</div>'; // end card

            // Connector arrow (except last)
            if ($index < $steps->count() - 1) {
                $html .= '<div class="flex items-center self-start mt-8 px-1">';
                $html .= '<div class="w-6 h-0.5 bg-gray-300"></div>';
                $html .= '<div class="text-gray-400 text-sm">▶</div>';
                $html .= '</div>';
            }

            $html .= '</div>'; // end flex col
        }

        $html .= '</div>';
        $html .= '</div>';

        return new HtmlString($html);
    }
}
