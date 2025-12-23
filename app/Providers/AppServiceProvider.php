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
        // Force all URLs to use HTTPS in local environment, as assets and other URLs were being generated with HTTP
        // when using ngrok in local development.
        if (env('APP_ENV') === 'local') {
            URL::forceScheme('https');
        }

    }
}
