<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\AppSetting;
use App\Services\WhatsAppService;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Facades\Mail;

class NotificationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.notification-settings';

    public ?array $data = [];

    public array $waStatusData = [
        'connected' => false,
        'status_label' => 'UNKNOWN',
        'message' => 'Belum diperiksa',
        'qr_code' => null,
    ];

    public ?string $test_email_recipient = null;

    public ?string $test_wa_recipient = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-bell';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Notifikasi';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pengaturan Notifikasi Terpadu';
    }

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
                $value = json_decode((string) $value, true);
            }
            $state[$setting->key] = $value;
        }

        // Default values if missing
        $defaults = [
            'notify_system_enabled' => true,
            'notify_system_retention_days' => 30,
            'notify_event_sppb_created' => true,
            'notify_event_approval_requested' => true,
            'notify_event_approval_stage_updated' => true,
            'notify_event_sppb_approved' => true,
            'notify_event_sppb_rejected_revised' => true,
            'notify_event_goods_released' => true,

            'notify_email_enabled' => false,
            'mail_driver' => 'smtp',
            'mail_host' => '127.0.0.1',
            'mail_port' => 1025,
            'mail_username' => '',
            'mail_password' => '',
            'resend_api_key' => '',
            'mail_from_address' => 'no-reply@esppb.perusahaan.com',
            'mail_from_name' => 'E-SPPB Enterprise',

            'notify_wa_enabled' => false,
            'wa_server_url' => 'http://127.0.0.1:3000/send-message',
            'wa_api_secret' => '',
            'wa_sender_number' => '',
        ];

        foreach ($defaults as $key => $val) {
            if (! isset($state[$key])) {
                $state[$key] = $val;
            }
        }

        $this->form->fill($state);
        $this->checkWaStatus();

        $user = auth()->user();
        $this->test_email_recipient = $user?->email;
        $this->test_wa_recipient = $user?->phone;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('NotificationSettingsTabs')
                    ->tabs([
                        Tab::make('Notifikasi Sistem')
                            ->icon('heroicon-m-bell')
                            ->schema([
                                Section::make('Master Controls & Retensi Log')
                                    ->description('Pengaturan utama untuk notifikasi in-app lonceng dan pembersihan otomatis riwayat notifikasi.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('notify_system_enabled')
                                                ->label('Aktifkan Notifikasi Web UI Lonceng')
                                                ->helperText('Jika aktif, pengguna akan menerima notifikasi in-app lonceng di header panel.')
                                                ->required(),

                                            Select::make('notify_system_retention_days')
                                                ->label('Retensi Log Notifikasi')
                                                ->options([
                                                    30 => '30 Hari',
                                                    60 => '60 Hari',
                                                    90 => '90 Hari',
                                                ])
                                                ->helperText('Durasi penyimpan riwayat notifikasi sebelum dibersihkan otomatis.')
                                                ->required(),
                                        ]),
                                    ]),

                                Section::make('Checklist Matrix Event Notifikasi')
                                    ->description('Pilih peristiwa (events) mana saja yang akan memicu pengiriman notifikasi ke pengguna.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Toggle::make('notify_event_sppb_created')
                                                ->label('Pengajuan SPPB Baru')
                                                ->helperText('Dikirim saat pemohon berhasil mengajukan SPPB baru.'),

                                            Toggle::make('notify_event_approval_requested')
                                                ->label('Permintaan Persetujuan (Approver)')
                                                ->helperText('Dikirim ke Approver/Verifier saat membutuhkan tindakan persetujuan.'),

                                            Toggle::make('notify_event_approval_stage_updated')
                                                ->label('Update Persetujuan Antar-Tahap')
                                                ->helperText('Dikirim ke Pemohon saat SPPB berlanjut ke tahap approval berikutnya.'),

                                            Toggle::make('notify_event_sppb_approved')
                                                ->label('SPPB Disetujui Sepenuhnya (Final Approved)')
                                                ->helperText('Dikirim ke Pemohon saat SPPB disetujui sepenuhnya.'),

                                            Toggle::make('notify_event_sppb_rejected_revised')
                                                ->label('Permintaan Revisi / Penolakan SPPB')
                                                ->helperText('Dikirim ke Pemohon saat SPPB ditolak atau diminta revisi.'),

                                            Toggle::make('notify_event_goods_released')
                                                ->label('Penerbitan Surat Jalan / Pelepasan Barang (SAT)')
                                                ->helperText('Dikirim saat Surat Jalan (Goods Release) dibuat atau diterbitkan.'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Notifikasi Email')
                            ->icon('heroicon-m-envelope')
                            ->schema([
                                Section::make('Konfigurasi Pengiriman Email')
                                    ->description('Pengaturan pengiriman surel notifikasi (SMTP Server atau Resend API).')
                                    ->schema([
                                        Toggle::make('notify_email_enabled')
                                            ->label('Aktifkan Notifikasi Email')
                                            ->helperText('Jika aktif, sistem akan mengirim email otomatis ke pengguna terkait.')
                                            ->columnSpanFull(),

                                        Select::make('mail_driver')
                                            ->label('Metode Pengiriman (Mail Driver)')
                                            ->options([
                                                'smtp' => 'SMTP Server',
                                                'resend' => 'Resend API',
                                            ])
                                            ->required()
                                            ->default('smtp')
                                            ->live()
                                            ->columnSpanFull(),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('mail_host')
                                                    ->label('SMTP Host')
                                                    ->required(fn ($get) => $get('mail_driver') === 'smtp')
                                                    ->placeholder('smtp.mailtrap.io'),

                                                TextInput::make('mail_port')
                                                    ->label('Port SMTP')
                                                    ->numeric()
                                                    ->required(fn ($get) => $get('mail_driver') === 'smtp')
                                                    ->placeholder('587'),

                                                TextInput::make('mail_username')
                                                    ->label('Username SMTP')
                                                    ->nullable(),

                                                TextInput::make('mail_password')
                                                    ->label('Password SMTP')
                                                    ->password()
                                                    ->revealable()
                                                    ->nullable(),
                                            ])
                                            ->visible(fn ($get) => $get('mail_driver') === 'smtp')
                                            ->columnSpanFull(),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('resend_api_key')
                                                    ->label('Resend API Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->required(fn ($get) => $get('mail_driver') === 'resend')
                                                    ->placeholder('re_xxxxxxxx')
                                                    ->columnSpanFull(),
                                            ])
                                            ->visible(fn ($get) => $get('mail_driver') === 'resend')
                                            ->columnSpanFull(),

                                        Grid::make(2)->schema([
                                            TextInput::make('mail_from_address')
                                                ->label('Email Pengirim (Sender Email)')
                                                ->email()
                                                ->required()
                                                ->default('no-reply@esppb.perusahaan.com'),

                                            TextInput::make('mail_from_name')
                                                ->label('Nama Pengirim (Sender Name)')
                                                ->required()
                                                ->default('E-SPPB Enterprise'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Notifikasi WhatsApp')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->schema([
                                Section::make('Setup OpenWA Gateway')
                                    ->description('Integrasi WhatsApp gateway berbasis OpenWA Node.js server.')
                                    ->schema([
                                        Toggle::make('notify_wa_enabled')
                                            ->label('Aktifkan Notifikasi WhatsApp')
                                            ->helperText('Jika aktif, notifikasi akan dikirimkan langsung ke nomor WA pengguna.')
                                            ->columnSpanFull(),

                                        Grid::make(3)->schema([
                                            TextInput::make('wa_server_url')
                                                ->label('Server URL Gateway')
                                                ->required()
                                                ->url()
                                                ->default('http://127.0.0.1:3000/send-message')
                                                ->placeholder('http://127.0.0.1:3000/send-message')
                                                ->columnSpan(2),

                                            TextInput::make('wa_sender_number')
                                                ->label('Nomor Bot WA Pengirim')
                                                ->tel()
                                                ->nullable()
                                                ->placeholder('6281234567890'),

                                            TextInput::make('wa_api_secret')
                                                ->label('API Secret Token Header')
                                                ->password()
                                                ->revealable()
                                                ->nullable()
                                                ->placeholder('Token Rahasia Header OpenWA')
                                                ->columnSpanFull(),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function checkWaStatus(): void
    {
        /** @var WhatsAppService $waService */
        $waService = app(WhatsAppService::class);
        $this->waStatusData = $waService->getStatus();
    }

    public function sendTestEmail(): void
    {
        $recipient = $this->test_email_recipient;

        if (empty($recipient)) {
            Notification::make()
                ->title('Gagal Mengirim Email Uji Coba')
                ->body('Silakan masukkan alamat email penerima terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        try {
            $data = $this->form->getState();
            $driver = $data['mail_driver'] ?? 'smtp';
            $fromAddress = $data['mail_from_address'] ?? 'no-reply@esppb.perusahaan.com';
            $fromName = $data['mail_from_name'] ?? 'E-SPPB Enterprise';

            // Dynamic mail configuration
            config([
                'mail.default' => $driver,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            if ($driver === 'smtp') {
                config([
                    'mail.mailers.smtp.host' => $data['mail_host'] ?? '127.0.0.1',
                    'mail.mailers.smtp.port' => (int) ($data['mail_port'] ?? 1025),
                    'mail.mailers.smtp.username' => $data['mail_username'] ?? '',
                    'mail.mailers.smtp.password' => $data['mail_password'] ?? '',
                ]);
            } elseif ($driver === 'resend') {
                config([
                    'resend.api_key' => $data['resend_api_key'] ?? '',
                ]);
            }

            Mail::purge();

            Mail::raw("Halo,\n\nIni adalah email uji coba dari modul Pengaturan Notifikasi E-SPPB Enterprise.\nPengaturan pengiriman email Anda berfungsi dengan baik!", function ($message) use ($recipient, $fromAddress, $fromName) {
                $message->to($recipient)
                    ->from($fromAddress, $fromName)
                    ->subject('[UJI COBA] Notifikasi Email E-SPPB Enterprise');
            });

            Notification::make()
                ->title('Email Uji Coba Berhasil Dikirim')
                ->body("Email uji coba telah dikirim ke: {$recipient}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Mengirim Email Uji Coba')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function sendTestWa(): void
    {
        $recipient = $this->test_wa_recipient;

        if (empty($recipient)) {
            Notification::make()
                ->title('Gagal Mengirim WA Uji Coba')
                ->body('Silakan masukkan nomor HP/WhatsApp penerima terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        /** @var WhatsAppService $waService */
        $waService = app(WhatsAppService::class);
        $success = $waService->sendTestMessage($recipient);

        if ($success) {
            Notification::make()
                ->title('WhatsApp Uji Coba Berhasil Dikirim')
                ->body("Pesan uji coba dikirim ke nomor: {$recipient}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal Mengirim WhatsApp Uji Coba')
                ->body('Koneksi OpenWA Gateway gagal atau tidak merespons. Periksa log sistem untuk detail.')
                ->danger()
                ->send();
        }
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        $types = [
            'notify_system_enabled' => 'boolean',
            'notify_system_retention_days' => 'integer',
            'notify_event_sppb_created' => 'boolean',
            'notify_event_approval_requested' => 'boolean',
            'notify_event_approval_stage_updated' => 'boolean',
            'notify_event_sppb_approved' => 'boolean',
            'notify_event_sppb_rejected_revised' => 'boolean',
            'notify_event_goods_released' => 'boolean',

            'notify_email_enabled' => 'boolean',
            'mail_driver' => 'string',
            'mail_host' => 'string',
            'mail_port' => 'integer',
            'mail_username' => 'string',
            'mail_password' => 'string',
            'resend_api_key' => 'string',
            'mail_from_address' => 'string',
            'mail_from_name' => 'string',

            'notify_wa_enabled' => 'boolean',
            'wa_server_url' => 'string',
            'wa_api_secret' => 'string',
            'wa_sender_number' => 'string',
        ];

        foreach ($formData as $key => $value) {
            $type = $types[$key] ?? 'string';
            AppSetting::set($key, $value, 'notification', $type);
        }

        $this->checkWaStatus();

        Notification::make()
            ->title('Pengaturan Notifikasi Berhasil Disimpan')
            ->success()
            ->send();
    }
}
