<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\EmailChangeRequests\EmailChangeRequestResource;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Filament\Actions\Action;
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
                            }),
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

        $user->save();

        Notification::make()
            ->title('Berhasil')
            ->body('Profil Anda berhasil diperbarui.')
            ->success()
            ->send();

        // Refresh form to show current information
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
