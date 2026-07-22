<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\MyProfile;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\AppSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public const THEME_PRESETS = [
        'default' => [
            'color' => '#2563EB',
            'font' => 'Inter',
            'gray' => Color::Slate,
        ],
        'nord' => [
            'color' => '#0D9488',
            'font' => 'Inter',
            'gray' => Color::Slate,
        ],
        'sunset' => [
            'color' => '#EA580C',
            'font' => 'Outfit',
            'gray' => Color::Zinc,
        ],
        'forest' => [
            'color' => '#059669',
            'font' => 'Plus Jakarta Sans',
            'gray' => Color::Stone,
        ],
        'dracula' => [
            'color' => '#9333EA',
            'font' => 'Outfit',
            'gray' => Color::Zinc,
        ],
        'min' => [
            'color' => '#475569',
            'font' => 'Plus Jakarta Sans',
            'gray' => Color::Neutral,
        ],
    ];

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(CustomLogin::class)
            ->brandName(fn () => AppSetting::get('app_custom_name', 'E-SPPB Enterprise'))
            ->brandLogo(function () {
                $isLogin = request()->routeIs('filament.admin.auth.login');
                if ($isLogin) {
                    $loginLogo = AppSetting::get('logo_login');
                    if ($loginLogo) {
                        return asset('storage/'.$loginLogo);
                    }
                }
                $themeLogo = AppSetting::get('logo_light');
                if ($themeLogo) {
                    return asset('storage/'.$themeLogo);
                }

                return $isLogin ? asset('images/logo-lanscape.png') : asset('images/logo.png');
            })
            ->brandLogoHeight(fn () => AppSetting::get('logo_height', 36).'px')
            ->favicon(function () {
                $favicon = AppSetting::get('logo_favicon');

                return $favicon ? asset('storage/'.$favicon) : asset('images/logo.png');
            })
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Profil & Pengaturan Akun')
                    ->url(fn (): string => MyProfile::getUrl())
                    ->icon('heroicon-o-user'),
            ])
            ->colors(function () {
                $user = auth()->user();
                $preset = $user?->theme_preset ?? 'default';

                $primaryColor = Color::hex('#2563EB');
                $grayColor = Color::Slate;

                if ($preset === 'custom') {
                    $primaryColor = $user->theme_color ? Color::hex($user->theme_color) : Color::hex('#2563EB');
                } elseif (isset(self::THEME_PRESETS[$preset])) {
                    $primaryColor = Color::hex(self::THEME_PRESETS[$preset]['color']);
                    $grayColor = self::THEME_PRESETS[$preset]['gray'];
                }

                return [
                    'primary' => $primaryColor,
                    'success' => Color::hex('#16A34A'),
                    'warning' => Color::hex('#F59E0B'),
                    'danger' => Color::hex('#DC2626'),
                    'info' => Color::hex('#0891B2'),
                    'gray' => $grayColor,
                ];
            })
            ->font(function () {
                $user = auth()->user();
                $preset = $user?->theme_preset ?? 'default';

                if ($preset === 'custom') {
                    return $user->theme_font ?? 'Inter';
                }

                if (isset(self::THEME_PRESETS[$preset])) {
                    return self::THEME_PRESETS[$preset]['font'];
                }

                return 'Inter';
            })
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Transaksi')
                    ->label('Transaksi')
                    ->icon('heroicon-o-document-text')
                    ->collapsed(false),
                NavigationGroup::make('Workflow')
                    ->label('Workflow')
                    ->icon('heroicon-o-arrow-path')
                    ->collapsed(false),
                NavigationGroup::make('Master Data')
                    ->label('Master Data')
                    ->icon('heroicon-o-circle-stack')
                    ->collapsed(true),
                NavigationGroup::make('Sistem')
                    ->label('Sistem & Konfigurasi')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                CheckMaintenanceMode::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
