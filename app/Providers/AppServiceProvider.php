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
        // Render sirve Laravel detrás de un proxy TLS. Forzar HTTPS en
        // producción evita que @vite(), asset() y route() emitan enlaces http.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
