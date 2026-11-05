<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KepalabPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kepalab')
            ->path('kepalab')
            ->login()
            ->brandName('Inventaris SMK — Lab & Bengkel')
            ->brandLogo(fn () => view('filament.brand'))
            ->darkMode(true)
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make()
                    ->label('Kembali ke Panel Utama Admin')
                    ->url('/admin')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('filament.kepalab.components.role-switcher')
            )
            ->colors([
                'primary' => Color::Teal,
                'gray'    => Color::Slate,
            ])
            ->font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')
            ->navigationGroups([
                NavigationGroup::make('Operasional Toolman')->collapsible(false),
                NavigationGroup::make('Manajemen Kepala Lab')->collapsible(false),
                NavigationGroup::make('Inventaris & Monitoring')->collapsible(false),
            ])
            ->discoverResources(in: app_path('Filament/Kepalab/Resources'), for: 'App\\Filament\\Kepalab\\Resources')
            ->discoverPages(in: app_path('Filament/Kepalab/Pages'), for: 'App\\Filament\\Kepalab\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Kepalab/Widgets'), for: 'App\\Filament\\Kepalab\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
