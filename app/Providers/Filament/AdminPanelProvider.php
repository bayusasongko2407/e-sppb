<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\MyProfile;
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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(CustomLogin::class)
            ->brandName('E-SPPB Enterprise')
            ->brandLogo(fn () => request()->routeIs('filament.admin.auth.login') ? asset('images/logo-lanscape.png') : asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo.png'))
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Profil & Pengaturan Akun')
                    ->url(fn (): string => MyProfile::getUrl())
                    ->icon('heroicon-o-user'),
            ])

            ->colors([
                'primary' => Color::hex('#2563EB'),
                'success' => Color::hex('#16A34A'),
                'warning' => Color::hex('#F59E0B'),
                'danger' => Color::hex('#DC2626'),
                'info' => Color::hex('#0891B2'),
                'gray' => Color::Slate,
            ])
            ->font('Inter')
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
