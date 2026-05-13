<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Importante
use Illuminate\Support\Facades\App; // <--- AÑADE ESTA LÍNEA

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
        //
        // La barra invertida delante de \App y \URL es la clave
        if (!\App::environment('local')) {
            \URL::forceScheme('https');
        }
    }
}
