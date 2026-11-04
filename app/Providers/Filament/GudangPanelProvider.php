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

class GudangPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('gudang')
            ->path('gudang')
            ->login()
            ->brandName('Inventaris SMK — Gudang ATK')
            ->brandLogo(fn () => view('filament.brand'))
            ->darkMode(true)
            ->colors([
                'primary' => Color::Emerald,
                'gray'    => Color::Slate,
            ])
            ->font('Inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap')
            ->navigationGroups([
                NavigationGroup::make('Ruang dan Master')->collapsible(false),
                NavigationGroup::make('Inventaris dan Stok')->collapsible(false),
                NavigationGroup::make('Permintaan dari Unit')->collapsible(false),
                NavigationGroup::make('Anggaran Restock')->collapsible(false),
                NavigationGroup::make('Laporan')->collapsible(false),
            ])
            ->discoverResources(in: app_path('Filament/Gudang/Resources'), for: 'App\\Filament\\Gudang\\Resources')
            ->discoverPages(in: app_path('Filament/Gudang/Pages'), for: 'App\\Filament\\Gudang\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Gudang/Widgets'), for: 'App\\Filament\\Gudang\\Widgets')
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
