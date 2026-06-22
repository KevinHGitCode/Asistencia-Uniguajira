<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();
    }

    /**
     * Limitadores de tasa con nombre (ADR-0005). Los límites viven en
     * config/throttle.php y se leen por petición para poder ajustarlos
     * (incluido en pruebas) sin reiniciar la app.
     */
    protected function configureRateLimiters(): void
    {
        // Registro público de asistencia por QR: por IP + slug del evento.
        RateLimiter::for('attendance', function (Request $request) {
            $slug = (string) $request->route('slug');

            return Limit::perMinute((int) config('throttle.attendance'))
                ->by($request->ip().'|'.$slug);
        });

        // Páginas públicas de acceso/confirmación: por IP.
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute((int) config('throttle.public'))->by($request->ip());
        });

        // Endpoints de estadística: por usuario autenticado (o IP de respaldo).
        RateLimiter::for('api-stats', function (Request $request) {
            return Limit::perMinute((int) config('throttle.api_stats'))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });

        // Descarga de PDF de asistencia: por usuario autenticado (o IP de respaldo).
        RateLimiter::for('pdf', function (Request $request) {
            return Limit::perMinute((int) config('throttle.pdf'))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
