<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Tables\Table;
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
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme(scheme: 'https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::$defaultNumberLocale = env('APP_LOCALE');
        Table::$defaultCurrency = 'BRL';
        
        Number::useLocale(env('APP_LOCALE'));
        Carbon::setLocale(env('APP_LOCALE'));
    }
}
