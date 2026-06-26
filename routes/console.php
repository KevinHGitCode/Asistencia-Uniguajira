<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas programadas (ADR-0004 + ADR-0018)
|--------------------------------------------------------------------------
|
| En Hostinger (hosting compartido, sin workers permanentes) basta con UN cron:
|
|   * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
|
| Ese tick dispara: el procesamiento de la cola (parseo encolado de imports),
| el escaneo de avisos de eventos y las limpiezas de retención.
|
*/

// Procesa la cola (parseo encolado de importaciones) y termina cuando se vacía.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=280')
    ->everyMinute()
    ->withoutOverlapping();

// Crea avisos in-app de eventos próximos / por finalizar.
Schedule::command('notifications:escanear-eventos')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Retención: limpieza diaria de notificaciones leídas y lotes ya procesados.
Schedule::command('notifications:limpiar')->dailyAt('03:00');
Schedule::command('imports:limpiar')->dailyAt('03:10');
