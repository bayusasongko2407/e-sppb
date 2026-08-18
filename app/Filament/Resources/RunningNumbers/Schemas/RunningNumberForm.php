<?php

namespace App\Filament\Resources\RunningNumbers\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class RunningNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Penomoran Otomatis')
                    ->description('Atur format penomoran dokumen di sini.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('document_type')
                                ->label('Tipe Dokumen')
                                ->options([
                                    'SPPB' => 'SPPB (Surat Permintaan Pengeluaran Barang)',
                                    'GR' => 'Barang Keluar (Goods Release)',
                                ])
                                ->required()
                                ->helperText('Pilih jenis dokumen yang menggunakan format ini.'),

                            Select::make('period_type')
                                ->label('Periode Reset Nomor')
                                ->options([
                                    'MONTHLY' => 'Bulanan (Reset ke 1 Setiap Bulan Baru)',
                                    'YEARLY' => 'Tahunan (Reset ke 1 Setiap Tahun Baru)',
                                    'DAILY' => 'Harian (Reset ke 1 Setiap Hari Baru)',
                                    'NEVER' => 'Kontinu / Tanpa Reset (Nomor Terus Berjalan)',
                                ])
                                ->default('MONTHLY')
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $key = match ($state) {
                                        'MONTHLY' => date('Y-m'),
                                        'YEARLY' => date('Y'),
                                        'DAILY' => date('Y-m-d'),
                                        'NEVER' => 'GLOBAL',
                                        default => date('Y-m'),
                                    };
                                    $set('period_key', $key);
                                })
                                ->helperText('Pilih frekuensi nomor urut akan di-reset otomatis kembali ke 1.'),

                            TextInput::make('period_key')
                                ->label('Kunci Periode Terdaftar')
                                ->required()
                                ->placeholder('Contoh: 2026-08')
                                ->helperText('Kunci periode otomatis menyesuaikan tipe reset (misal Bulanan = 2026-08, Tahunan = 2026, Harian = 2026-08-18).'),

                            Select::make('plant_id')
                                ->label('Pabrik / Plant')
                                ->relationship('plant', 'name')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('department_id', null)),

                            Select::make('department_id')
                                ->label('Departemen (Opsional)')
                                ->relationship(
                                    name: 'department',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: function ($query, Get $get) {
                                        $query->with('plant');
                                        $plantId = $get('plant_id');
                                        if ($plantId) {
                                            $query->where('plant_id', $plantId);
                                        }

                                        return $query;
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->plant?->code}] - {$record->name}")
                                ->default(null)
                                ->helperText('Kosongkan jika format berlaku untuk semua departemen di plant ini.'),

                            TextInput::make('prefix')
                                ->label('Prefix / Format Penomoran')
                                ->required()
                                ->live(debounce: 500)
                                ->placeholder('Contoh: SPPB/{PLN}/{DEP}/{YY}/{MM}/')
                                ->helperText('Gunakan {DD} Tanggal, {MM} Bulan, {YY} Tahun (2 digit), {YYYY} Tahun (4 digit), {DEP} Kode Departemen, {PLN} Kode Plant. Akhiri dengan garis miring atau strip jika perlu.'),

                            TextInput::make('digits')
                                ->label('Jumlah Digit')
                                ->required()
                                ->live(debounce: 500)
                                ->numeric()
                                ->default(5)
                                ->helperText('Jumlah digit untuk nomor urut (contoh: 5 = 00001).'),

                            Placeholder::make('preview')
                                ->label('Preview Penomoran (Simulasi)')
                                ->content(function (mixed $get) {
                                    $prefix = $get('prefix');
                                    if (empty($prefix)) {
                                        return '-';
                                    }
                                    $digits = (int) ($get('digits') ?: 5);
                                    $lastNumber = (int) ($get('last_number') ?: 0);

                                    $prefix = str_replace('{DD}', date('d'), $prefix);
                                    $prefix = str_replace('{MM}', date('m'), $prefix);
                                    $prefix = str_replace('{YY}', date('y'), $prefix);
                                    $prefix = str_replace('{YYYY}', date('Y'), $prefix);
                                    $prefix = str_replace('{DEP}', 'ENG', $prefix);
                                    $prefix = str_replace('{PLN}', 'JKT', $prefix);

                                    return new HtmlString('<strong class="text-xl text-primary-600">'.$prefix.str_pad((string) ($lastNumber + 1), $digits > 0 ? $digits : 5, '0', STR_PAD_LEFT).'</strong>');
                                })
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Status & Counter')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('last_number')
                                ->label('Nomor Terakhir')
                                ->required()
                                ->live(debounce: 500)
                                ->numeric()
                                ->default(0)
                                ->helperText('Sistem akan melanjutkan dari nomor ini.'),

                            Toggle::make('is_active')
                                ->label('Aktif digunakan')
                                ->default(true)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
