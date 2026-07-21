<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\EmailChangeRequests\EmailChangeRequestResource;
use App\Models\EmailChangeRequest;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $title = 'Profil & Pengaturan Akun';

    protected static ?string $slug = 'my-profile';

    protected string $view = 'filament.pages.my-profile';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'theme_preset' => $user->theme_preset ?? 'default',
            'theme_color' => $user->theme_color ?? '#2563EB',
            'theme_font' => $user->theme_font ?? 'Inter',
        ]);
    }

    public function form(Schema $form): Schema
    {
        $user = auth()->user();

        return $form
            ->schema([
                Section::make('Informasi Pribadi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Nomor Telepon / WhatsApp')
                            ->tel()
                            ->placeholder('Contoh: 081234567890')
                            ->helperText('Nomor WhatsApp aktif Anda untuk menerima notifikasi pesan singkat dari sistem E-SPPB.')
                            ->nullable(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->helperText(function () use ($user) {
                                $pending = EmailChangeRequest::where('user_id', $user->id)
                                    ->where('status', 'PENDING')
                                    ->first();
                                if ($pending) {
                                    return "Permintaan perubahan email ke {$pending->new_email} sedang menunggu persetujuan Super Admin.";
                                }

                                return 'Perubahan email memerlukan persetujuan dari Super Admin sebelum aktif.';
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Ubah Kata Sandi')
                    ->description('Kosongkan jika Anda tidak ingin mengubah kata sandi.')
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata Sandi Baru')
                            ->password()
                            ->rule(
                                Password::min(8)
                                    ->mixedCase()
                                    ->letters()
                                    ->numbers()
                                    ->symbols()
                            )
                            ->rule(function () use ($user) {
                                return function ($attribute, $value, $fail) use ($user) {
                                    if ($value && Hash::check($value, $user->password)) {
                                        $fail('Password baru tidak boleh sama dengan password sebelumnya.');
                                    }
                                };
                            }),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Kata Sandi Baru')
                            ->password()
                            ->requiredWith('password')
                            ->same('password'),
                    ])->columns(2),

                Section::make('Pengaturan Tema Aplikasi')
                    ->description('Sesuaikan tema visual dan jenis huruf (font) untuk kenyamanan Anda.')
                    ->schema([
                        Select::make('theme_preset')
                            ->label('Tema Preset (Hasnayeen Style)')
                            ->options([
                                'default' => 'Default (Biru Corporate)',
                                'nord' => 'Nord (Teal & Slate)',
                                'sunset' => 'Sunset (Orange & Zinc)',
                                'forest' => 'Forest (Emerald & Stone)',
                                'dracula' => 'Dracula (Purple & Dark Slate)',
                                'min' => 'Min (Slate & Neutral)',
                                'custom' => 'Kustom (Pilih Warna & Font Sendiri)',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $presets = AdminPanelProvider::THEME_PRESETS;
                                if (isset($presets[$state])) {
                                    $set('theme_color', $presets[$state]['color']);
                                    $set('theme_font', $presets[$state]['font']);
                                }
                            })
                            ->required()
                            ->columnSpanFull(),
                        Select::make('theme_color')
                            ->label('Warna Tema Utama')
                            ->options([
                                '#2563EB' => 'Biru Corporate (Blue)',
                                '#0D9488' => 'Teal Nord',
                                '#EA580C' => 'Orange Sunset',
                                '#059669' => 'Hijau Forest (Emerald)',
                                '#9333EA' => 'Ungu Dracula (Purple)',
                                '#475569' => 'Slate Min',
                                '#7C3AED' => 'Ungu Premium (Violet)',
                                '#D97706' => 'Kuning Amber (Amber)',
                                '#E11D48' => 'Merah Rose (Rose)',
                                '#0891B2' => 'Teal Modern (Teal)',
                            ])
                            ->disabled(fn (callable $get) => $get('theme_preset') !== 'custom')
                            ->required(fn (callable $get) => $get('theme_preset') === 'custom')
                            ->dehydrated(),
                        Select::make('theme_font')
                            ->label('Jenis Huruf (Font)')
                            ->options([
                                'Inter' => 'Inter (Modern & Bersih)',
                                'Outfit' => 'Outfit (Trendy & Bulat)',
                                'Plus Jakarta Sans' => 'Plus Jakarta Sans (Profesional & Dinamis)',
                                'Roboto' => 'Roboto (Fungsional)',
                                'Open Sans' => 'Open Sans (Klasik)',
                            ])
                            ->disabled(fn (callable $get) => $get('theme_preset') !== 'custom')
                            ->required(fn (callable $get) => $get('theme_preset') === 'custom')
                            ->dehydrated(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $user = auth()->user();
        $formData = $this->form->getState();

        $user->name = $formData['name'];
        $user->phone = $formData['phone'] ?? null;

        // Handle email change request
        if ($formData['email'] !== $user->email) {
            // Check if there is already a pending request
            $existingPending = EmailChangeRequest::where('user_id', $user->id)
                ->where('status', 'PENDING')
                ->first();

            if ($existingPending) {
                if ($existingPending->new_email !== $formData['email']) {
                    $existingPending->update([
                        'new_email' => $formData['email'],
                        'requested_at' => now(),
                    ]);
                }
            } else {
                EmailChangeRequest::create([
                    'user_id' => $user->id,
                    'old_email' => $user->email,
                    'new_email' => $formData['email'],
                    'status' => 'PENDING',
                ]);
            }

            // Notify all super admins
            $superAdmins = User::role('super_admin')->get();
            foreach ($superAdmins as $admin) {
                Notification::make()
                    ->title('Pengajuan Perubahan Email')
                    ->body("Pengguna {$user->name} mengajukan perubahan email menjadi {$formData['email']}.")
                    ->icon('heroicon-o-envelope')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Pengajuan')
                            ->url(EmailChangeRequestResource::getUrl('index')),
                    ])
                    ->sendToDatabase($admin);
            }

            Notification::make()
                ->title('Informasi')
                ->body('Perubahan email berhasil diajukan dan memerlukan persetujuan Super Admin.')
                ->warning()
                ->send();
        }

        // Handle password change
        if (! empty($formData['password'])) {
            $user->password = Hash::make($formData['password']);

            Notification::make()
                ->title('Berhasil')
                ->body('Kata sandi berhasil diperbarui.')
                ->success()
                ->send();
        }

        $preset = $formData['theme_preset'];
        $user->theme_preset = $preset;

        if ($preset === 'custom') {
            $user->theme_color = $formData['theme_color'];
            $user->theme_font = $formData['theme_font'];
        } else {
            $presets = AdminPanelProvider::THEME_PRESETS;
            if (isset($presets[$preset])) {
                $user->theme_color = $presets[$preset]['color'];
                $user->theme_font = $presets[$preset]['font'];
            }
        }
        $user->save();

        Notification::make()
            ->title('Berhasil')
            ->body('Profil dan Pengaturan Tema Anda berhasil diperbarui.')
            ->success()
            ->send();

        // Redirect to reload the page and apply the new theme immediately
        $this->redirect(static::getUrl());
    }
}
