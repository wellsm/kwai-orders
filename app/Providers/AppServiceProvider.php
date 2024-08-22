<?php

namespace App\Providers;

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
        
        Number::useLocale(env('APP_LOCALE'));
    }
}
