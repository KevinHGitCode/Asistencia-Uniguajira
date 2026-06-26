<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Centro de notificaciones in-app (ADR-0018)
    |--------------------------------------------------------------------------
    |
    | Ventanas y retención de los avisos in-app. Todas en minutos / días.
    |
    */

    // Cuántos minutos antes de su inicio se avisa de un evento "próximo".
    'event_starting_window' => (int) env('NOTIF_EVENT_STARTING_WINDOW', 60),

    // Cuántos minutos antes de su fin se avisa de un evento "por finalizar".
    'event_ending_window' => (int) env('NOTIF_EVENT_ENDING_WINDOW', 30),

    // Días que se conservan las notificaciones ya leídas antes de purgarlas.
    'read_retention_days' => (int) env('NOTIF_READ_RETENTION_DAYS', 30),

    // Días que se conservan los lotes de importación ya procesados
    // (aprobado / rechazado / error) antes de purgarlos.
    'import_retention_days' => (int) env('IMPORT_RETENTION_DAYS', 30),

];
