<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Schema::defaultStringLength(255);
        // Register anonymous components
        Blade::anonymousComponentPath(resource_path('views/components'));

        // Force HTTPS scheme in production (required when behind reverse proxy/Cloudflare)
        // Prevents 419 CSRF errors caused by HTTP/HTTPS scheme mismatch on session cookies
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
