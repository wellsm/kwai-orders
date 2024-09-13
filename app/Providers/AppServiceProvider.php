<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme(scheme: 'https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::$defaultNumberLocale = config('app.locale');
        Table::$defaultCurrency = 'BRL';
        
        Number::useLocale(config('app.locale'));
        Carbon::setLocale(config('app.locale'));

        FilamentView::registerRenderHook(
            name: PanelsRenderHook::HEAD_END,
            hook: fn (): string => Blade::render("@vite('resources/css/app.css')")
        );
    }
}
