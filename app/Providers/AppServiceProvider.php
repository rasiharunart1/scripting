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
        // Force HTTPS pada semua URL yang di-generate Laravel (route(), asset(), url())
        // Mencegah Mixed Content error saat diakses via HTTPS
        if ($this->app->environment('production') || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
