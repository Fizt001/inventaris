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

class KepsekPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kepsek')
            ->path('kepsek')
            ->login()
            ->brandName('Inventaris SMK — Kepala Sekolah')
            ->brandLogo(fn () => view('filament.brand'))
            ->darkMode(true)
            ->colors([
                'primary' => Color::Violet,
                'gray'    => Color::Slate,
            ])
            ->font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')
            ->navigationGroups([
                NavigationGroup::make('Dashboard Eksekutif'),
                NavigationGroup::make('Laporan Inventaris'),
                NavigationGroup::make('Laporan Anggaran'),
                NavigationGroup::make('Laporan Aset'),
            ])
            ->discoverResources(in: app_path('Filament/Kepsek/Resources'), for: 'App\\Filament\\Kepsek\\Resources')
            ->discoverPages(in: app_path('Filament/Kepsek/Pages'), for: 'App\\Filament\\Kepsek\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Kepsek/Widgets'), for: 'App\\Filament\\Kepsek\\Widgets')
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
