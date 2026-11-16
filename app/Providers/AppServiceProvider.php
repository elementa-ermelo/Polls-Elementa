<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inloggen: 10 pogingen per minuut en 30 per uur per IP-adres, over alle gebruikersnamen heen
        // (de rem van AuthenticatesUsers telt per naam en IP, dus raden over veel namen ging ongeremd).
        \Illuminate\Support\Facades\RateLimiter::for('login', fn (\Illuminate\Http\Request $r) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by('min|' . $r->ip()),
            \Illuminate\Cache\RateLimiting\Limit::perHour(30)->by('uur|' . $r->ip()),
        ]);

        // Always use HTTPS for URL generation
        \URL::forceScheme('https');
        \URL::forceRootUrl(config('app.url'));
    }
}
