<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
    public function boot()
    {
        // Auto-detect APP_URL
        if (isset($_SERVER['HTTP_HOST'])) {
            $proto = $_SERVER['REQUEST_SCHEME'] ?? 'http';
            $host = $_SERVER['HTTP_HOST'];
            config(['app.url' => "$proto://$host"]);
        }
    }
}
