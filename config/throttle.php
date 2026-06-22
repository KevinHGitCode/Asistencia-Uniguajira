<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Límites de tasa por limitador con nombre (ADR-0005)
    |--------------------------------------------------------------------------
    |
    | Peticiones permitidas por minuto para cada limitador definido en
    | App\Providers\AppServiceProvider. Ajustables sin tocar código vía .env.
    |
    | Recuerda: este es el rate limit de APLICACIÓN. No sustituye la defensa
    | de borde (CDN/WAF) contra DDoS volumétrico — ver ADR-0005.
    |
    */

    // Registro público de asistencia por QR (POST). Clave: IP + slug del evento.
    // Frena fuerza bruta de documentos sin estorbar a asistentes con su propio móvil.
    'attendance' => (int) env('THROTTLE_ATTENDANCE', 30),

    // Páginas públicas de acceso/confirmación (GET). Clave: IP. Límite holgado.
    'public' => (int) env('THROTTLE_PUBLIC', 60),

    // Endpoints de estadística (/api/statistics/*). Clave: usuario (o IP).
    // Generoso a propósito: el panel dispara ~17 peticiones en paralelo por carga.
    'api_stats' => (int) env('THROTTLE_API_STATS', 300),

    // Descarga de PDF de asistencia (pesada). Clave: usuario (o IP).
    'pdf' => (int) env('THROTTLE_PDF', 10),

];
