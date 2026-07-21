<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class AppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.app-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Aplikasi';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pengaturan Aplikasi';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = AppSetting::all();
        $state = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            if ($setting->type === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($setting->type === 'integer') {
                $value = (int) $value;
            } elseif ($setting->type === 'json') {
                $value = json_decode($value, true);
            }
            $state[$setting->key] = $value;
        }

        $this->form->fill($state);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Identitas Perusahaan')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('company_name')
                                        ->label('Nama Resmi Perusahaan')
                                        ->required()
                                        ->maxLength(200),
                                    TextInput::make('company_tax_id')
                                        ->label('NPWP Perusahaan')
                                        ->maxLength(50),
                                    TextInput::make('company_phone')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->maxLength(50),
                                    TextInput::make('company_email')
                                        ->label('Email Perusahaan')
                                        ->email()
                                        ->maxLength(100),
                                    Textarea::make('company_address')
                                        ->label('Alamat Kantor Pusat')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->maxLength(500),
                                ]),
                            ]),

                        Tab::make('Visual & Branding')
                            ->icon('heroicon-m-paint-brush')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('app_custom_name')
                                        ->label('Nama Aplikasi Kustom')
                                        ->required()
                                        ->maxLength(100),
                                    ColorPicker::make('app_primary_color')
                                        ->label('Warna Tema Utama (Primary Color)')
                                        ->required(),
                                ]),

                                Section::make('Pengaturan Logo Aplikasi')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            FileUpload::make('logo_light')
                                                ->label('Logo Tema Terang')
                                                ->image()
                                                ->disk('public')
                                                ->directory('logos')
                                                ->helperText('Tampil saat menggunakan dashboard mode terang. Format: PNG, JPG, SVG.'),
                                            FileUpload::make('logo_dark')
                                                ->label('Logo Tema Gelap')
                                                ->image()
                                                ->disk('public')
                                                ->directory('logos')
                                                ->helperText('Tampil saat menggunakan dashboard mode gelap. Format: PNG, JPG, SVG.'),
                                            TextInput::make('logo_height')
                                                ->label('Tinggi Logo Dashboard (px)')
                                                ->numeric()
                                                ->required()
                                                ->default(36)
                                                ->helperText('Mengatur ukuran tinggi logo pada sidebar utama.'),
                                            FileUpload::make('logo_favicon')
                                                ->label('Favicon Browser')
                                                ->image()
                                                ->disk('public')
                                                ->directory('logos')
                                                ->helperText('Ikon kecil tab browser. Format: ICO, PNG, SVG.'),
                                            FileUpload::make('logo_login')
                                                ->label('Logo Halaman Login')
                                                ->image()
                                                ->disk('public')
                                                ->directory('logos')
                                                ->helperText('Tampil di tengah halaman login. Jika kosong, menggunakan Logo default.'),
                                        ]),
                                    ]),

                                Section::make('Pengaturan Logo Dokumen & PDF')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            FileUpload::make('logo_pdf')
                                                ->label('Logo untuk PDF')
                                                ->image()
                                                ->disk('public')
                                                ->directory('logos')
                                                ->columnSpan(2)
                                                ->helperText('Digunakan untuk kop surat resmi di file ekspor PDF. Gunakan resolusi tinggi.'),
                                            Select::make('logo_pdf_position')
                                                ->label('Posisi Logo PDF')
                                                ->options([
                                                    'left' => 'Kiri',
                                                    'center' => 'Tengah',
                                                    'right' => 'Kanan',
                                                ])
                                                ->required(),
                                            TextInput::make('logo_pdf_height')
                                                ->label('Tinggi Logo PDF (mm)')
                                                ->numeric()
                                                ->required()
                                                ->default(40)
                                                ->helperText('Ukuran tinggi logo saat dicetak di dokumen PDF.'),
                                            Toggle::make('logo_pdf_show_address')
                                                ->label('Tampilkan Alamat di Kop PDF')
                                                ->helperText('Apakah alamat perusahaan dicantumkan di samping logo pada kop PDF.'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Pengaturan Regional')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('regional_timezone')
                                        ->label('Zona Waktu (Timezone)')
                                        ->options([
                                            'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
                                            'Asia/Makassar' => 'Asia/Makassar (WITA)',
                                            'Asia/Jayapura' => 'Asia/Jayapura (WIT)',
                                        ])
                                        ->required(),
                                    Select::make('regional_date_format')
                                        ->label('Format Tanggal & Waktu')
                                        ->options([
                                            'd/m/Y' => 'd/m/Y (Contoh: 18/07/2026)',
                                            'Y-m-d' => 'Y-m-d (Contoh: 2026-07-18)',
                                            'd-m-Y' => 'd-m-Y (Contoh: 18-07-2026)',
                                        ])
                                        ->required(),
                                    TextInput::make('regional_currency_symbol')
                                        ->label('Simbol Mata Uang')
                                        ->required()
                                        ->maxLength(10),
                                    TextInput::make('regional_currency_code')
                                        ->label('Kode Mata Uang (ISO)')
                                        ->required()
                                        ->maxLength(10),
                                    Select::make('regional_thousand_separator')
                                        ->label('Pemisah Ribuan')
                                        ->options([
                                            '.' => 'Titik (.)',
                                            ',' => 'Koma (,)',
                                            ' ' => 'Spasi ( )',
                                        ])
                                        ->required(),
                                    Select::make('regional_decimal_separator')
                                        ->label('Pemisah Desimal')
                                        ->options([
                                            ',' => 'Koma (,)',
                                            '.' => 'Titik (.)',
                                        ])
                                        ->required(),
                                ]),
                            ]),

                        Tab::make('Keamanan & Sesi')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('security_session_timeout')
                                        ->label('Sesi Aktif Maksimum (Menit)')
                                        ->numeric()
                                        ->required()
                                        ->minValue(5)
                                        ->maxValue(1440),
                                    TextInput::make('security_login_limit')
                                        ->label('Batas Percobaan Login Gagal')
                                        ->numeric()
                                        ->required()
                                        ->minValue(3)
                                        ->maxValue(20),
                                    Toggle::make('security_strong_password')
                                        ->label('Wajibkan Password Kuat')
                                        ->helperText('Jika diaktifkan, password baru harus kombinasi huruf besar, kecil, angka, dan simbol.'),
                                ]),
                            ]),

                        Tab::make('Kendali Operasional')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Grid::make(1)->schema([
                                    Toggle::make('op_maintenance_mode')
                                        ->label('Aktifkan Mode Pemeliharaan (Maintenance Mode)')
                                        ->helperText('Jika diaktifkan, pengguna non-admin tidak dapat mengakses aplikasi utama.'),
                                    Textarea::make('op_maintenance_message')
                                        ->label('Pesan Pemeliharaan')
                                        ->rows(3)
                                        ->maxLength(500),
                                    Toggle::make('op_emergency_bypass')
                                        ->label('Bypass Approval Darurat (Global Emergency Bypass)')
                                        ->helperText('Bypass workflow otorisasi secara darurat (hanya untuk Super Admin jika terjadi kerusakan workflow).'),
                                ]),
                            ]),

                        Tab::make('Label & Istilah')
                            ->icon('heroicon-m-tag')
                            ->schema([
                                Section::make('Struktur Organisasi & Lokasi')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('label_plant')
                                                ->label('Label Plant / Pabrik')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_department')
                                                ->label('Label Departemen')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_location')
                                                ->label('Label Lokasi / Gudang')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Modul & Jenis Transaksi')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('label_sppb')
                                                ->label('Label SPPB')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_goods_release')
                                                ->label('Label Surat Jalan')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Kamus Logistik & Detail Barang')
                                    ->schema([
                                        Grid::make(7)->schema([
                                            TextInput::make('label_asset')
                                                ->label('Label Aset')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_item')
                                                ->label('Label Barang')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_unit')
                                                ->label('Label Satuan')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_qty')
                                                ->label('Label Qty / Jumlah')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_remarks')
                                                ->label('Label Keterangan')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_asset_barcode')
                                                ->label('Label Barcode / Kode')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_sppb_purpose')
                                                ->label('Label Keperluan')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Detail Pengiriman Surat Jalan')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            TextInput::make('label_driver_name')
                                                ->label('Label Nama Pengemudi')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_vehicle_number')
                                                ->label('Label No. Kendaraan')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_expedition_name')
                                                ->label('Label Nama Ekspedisi')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_delivery_date')
                                                ->label('Label Tanggal Pengiriman')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Status Dokumen & Alur Kerja')
                                    ->schema([
                                        Grid::make(5)->schema([
                                            TextInput::make('label_status_draft')
                                                ->label('Label Draft')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_status_pending')
                                                ->label('Label Menunggu Persetujuan')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_status_approved')
                                                ->label('Label Disetujui')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_status_rejected')
                                                ->label('Label Ditolak')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_status_revision')
                                                ->label('Label Revisi')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Aktor & Pihak Terkait')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('label_actor_requester')
                                                ->label('Label Pemohon')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_actor_approver')
                                                ->label('Label Penyetuju')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_actor_receiver')
                                                ->label('Label Penerima')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                                Section::make('Status Pengiriman')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('label_shipment_transit')
                                                ->label('Label Dalam Pengiriman')
                                                ->required()
                                                ->maxLength(50),
                                            TextInput::make('label_shipment_delivered')
                                                ->label('Label Terkirim')
                                                ->required()
                                                ->maxLength(50),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $types = [
            'company_name' => 'string',
            'company_address' => 'string',
            'company_phone' => 'string',
            'company_email' => 'string',
            'company_tax_id' => 'string',
            'app_custom_name' => 'string',
            'app_primary_color' => 'string',
            'security_session_timeout' => 'integer',
            'security_login_limit' => 'integer',
            'security_strong_password' => 'boolean',
            'regional_timezone' => 'string',
            'regional_date_format' => 'string',
            'regional_currency_symbol' => 'string',
            'regional_currency_code' => 'string',
            'regional_thousand_separator' => 'string',
            'regional_decimal_separator' => 'string',
            'op_maintenance_mode' => 'boolean',
            'op_maintenance_message' => 'string',
            'op_emergency_bypass' => 'boolean',
            'label_plant' => 'string',
            'label_department' => 'string',
            'label_location' => 'string',
            'label_sppb' => 'string',
            'label_goods_release' => 'string',
            'label_asset' => 'string',
            'label_item' => 'string',
            'label_unit' => 'string',
            'label_qty' => 'string',
            'label_remarks' => 'string',
            'label_driver_name' => 'string',
            'label_vehicle_number' => 'string',
            'label_expedition_name' => 'string',
            'label_delivery_date' => 'string',
            'label_status_draft' => 'string',
            'label_status_pending' => 'string',
            'label_status_approved' => 'string',
            'label_status_rejected' => 'string',
            'label_status_revision' => 'string',
            'label_actor_requester' => 'string',
            'label_actor_approver' => 'string',
            'label_actor_receiver' => 'string',
            'label_shipment_transit' => 'string',
            'label_shipment_delivered' => 'string',
            'label_asset_barcode' => 'string',
            'label_sppb_purpose' => 'string',
        ];

        foreach ($data as $key => $value) {
            $type = $types[$key] ?? 'string';
            $group = 'general';
            AppSetting::set($key, $value, $group, $type);
        }

        Notification::make()
            ->title('Pengaturan Aplikasi Berhasil Disimpan')
            ->success()
            ->send();
    }
}
