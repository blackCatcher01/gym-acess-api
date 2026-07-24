<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request

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
        RateLimiter::for('login', function (Request $request) {
            // Clé combinée téléphone + IP : limite le brute-force
            // même si l'attaquant change d'IP ou de numéro cible.
            return [
                Limit::perMinute(5)->by('login-tel-' . $request->input('telephone')),
                Limit::perMinute(20)->by('login-ip-' . $request->ip()),
            ];
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinutes(10)->by('otp-' . $request->input('telephone'));
        });
    }
}
