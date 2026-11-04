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

class WakasarprasPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('wakasarpras')
            ->path('wakasarpras')
            ->login()
            ->brandName('Inventaris SMK — Wakasarpras')
            ->brandLogo(fn () => view('filament.brand'))
            ->darkMode(true)
            ->colors([
                'primary' => Color::Amber,
                'gray'    => Color::Slate,
            ])
            ->font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')
            ->navigationGroups([
                NavigationGroup::make('Pengajuan Anggaran'),
                NavigationGroup::make('Monitoring Inventaris'),
                NavigationGroup::make('Laporan Konsolidasi'),
            ])
            ->discoverResources(in: app_path('Filament/Wakasarpras/Resources'), for: 'App\\Filament\\Wakasarpras\\Resources')
            ->discoverPages(in: app_path('Filament/Wakasarpras/Pages'), for: 'App\\Filament\\Wakasarpras\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Wakasarpras/Widgets'), for: 'App\\Filament\\Wakasarpras\\Widgets')
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
