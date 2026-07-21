<?php

namespace App\Filament\Pages;

use App\Models\ApiSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ApiSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected string $view = 'filament.pages.api-setting-page';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan API';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pengaturan API & Dokumentasi';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = ApiSetting::first();
        if ($setting) {
            $this->form->fill($setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Konfigurasi Lingkungan API')
                    ->schema([
                        Select::make('environment')
                            ->label('Environment')
                            ->options([
                                'sandbox' => 'Sandbox (Ujicoba)',
                                'production' => 'Production (Live)',
                            ])
                            ->required(),
                        Toggle::make('is_sandbox')
                            ->label('Aktifkan Mode Sandbox')
                            ->helperText('Jika aktif, mutasi data via API tidak akan mengubah status di aplikasi utama (simulasi).'),
                        Toggle::make('is_mock_approval_enabled')
                            ->label('Mock Approval')
                            ->helperText('Bypass workflow otorisasi dan langsung auto-approve (khusus environment sandbox).'),
                    ]),
                Section::make('Keamanan & Webhook')
                    ->schema([
                        TextInput::make('webhook_url')
                            ->label('URL Webhook API')
                            ->url()
                            ->nullable(),
                        TextInput::make('api_rate_limit')
                            ->label('Batas Permintaan per Menit (Rate Limit)')
                            ->numeric()
                            ->required()
                            ->default(60),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = ApiSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            ApiSetting::create($data);
        }

        Notification::make()
            ->title('Pengaturan API Berhasil Disimpan')
            ->success()
            ->send();
    }
}
