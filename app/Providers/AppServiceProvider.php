<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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

        FilamentAsset::register([
            Css::make('admin-css', Vite::asset('resources/css/app.css', 'build'))
        ]);
    }
}
