<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega índices en las columnas más consultadas por el módulo de estadísticas.
 *
 * En MySQL los FK ya tienen índice automático; en SQLite no.
 * Se usa try/catch para que la migración sea idempotente en cualquier motor.
 *
 * Columnas con mayor impacto en rendimiento:
 *  - events.date                   → filtro de rango en casi todas las queries
 *  - events.user_id                → FK + filtro por usuario
 *  - attendances.event_id          → FK + join principal de asistencias
 *  - attendances.participant_id    → FK + join principal de participantes
 *  - participants.program_id       → FK + join para gráficos de programa
 */
return new class extends Migration
{
    private array $indexes = [
        'events_date_index'                => ['events',       ['date']],
        'events_user_id_index'             => ['events',       ['user_id']],
        'attendances_event_id_index'       => ['attendances',  ['event_id']],
        'attendances_participant_id_index' => ['attendances',  ['participant_id']],
        'participants_program_id_index'    => ['participants',  ['program_id']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $name => [$table, $columns]) {
            try {
                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            } catch (\Exception) {
                // El índice ya existe (MySQL crea índices automáticos para FK)
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $name => [$table]) {
            try {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
            } catch (\Exception) {
                // El índice no existe o no es removible
            }
        }
    }
};
