<?php

namespace App\Providers\Filament;

use App\Enums\Role;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Models\Team;
use App\Models\User;
use App\Services\Settings\Config;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentLaravelLog\FilamentLaravelLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->loginRouteSlug('login')
            ->profile()
            ->when(fn () => Config::get('registration') ?? false, fn (Panel $panel) => $panel->registration())
            ->colors([
                'primary' => Color::Yellow,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->collapsibleNavigationGroups(false)
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ])
            ->tenant(Team::class, ownershipRelationship: 'team', slugAttribute: 'username')
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class)
            ->tenantMenuItems([
                'register' => MenuItem::make()
                    ->label('Adicionar Conta')
                    ->visible(fn () => $this->user()?->isRole(Role::Admin) ?? false),
                'profile' => MenuItem::make()
                    ->label('Alterar Conta')
                    ->visible(fn () => $this->user()?->isRole(Role::Admin) ?? false)
            ])
            ->plugins([
                FilamentLaravelLogPlugin::make()
                    ->navigationGroup('Sistema')
                    ->authorize(
                        fn() => $this->user()?->isRole(Role::SuperAdmin) ?? false
                    )
            ])
            ->spa()
            ->databaseTransactions();
    }

    private function user(): User|null
    {
        return Auth::user();
    }
}
