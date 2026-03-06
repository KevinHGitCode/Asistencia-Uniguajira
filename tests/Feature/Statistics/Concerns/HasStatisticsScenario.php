<?php

namespace Tests\Feature\Statistics\Concerns;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;

/**
 * Escenario de datos reutilizable para todos los tests de estadísticas.
 *
 * Datos fijos:
 * ─ Programa A: "Ingeniería de Sistemas"
 * ─ Programa B: "Administración de Empresas"
 * ─ Admin: crea los eventos
 * ─ Evento 1 (2026-01-10)  ─ dentro del periodo amplio
 * ─ Evento 2 (2026-03-01)  ─ dentro del periodo amplio Y del periodo estrecho
 * ─ Evento 3 (2025-11-01)  ─ FUERA del periodo amplio
 *
 * Participantes y asistencias:
 * ─ Alice (Estudiante, F, Víctimas,  Prog A) → Evento1 + Evento2 + Evento3
 * ─ Bob   (Docente,    M, null,       Prog B) → Evento1
 * ─ Carol (Estudiante, F, LGBTQ+,    Prog A) → Evento2
 *
 * Periodo amplio  (2026-01-01 a 2026-03-31):
 *   total_attendances  = 4   (Alice×2, Bob×1, Carol×1)
 *   total_participants = 3   (Alice, Bob, Carol)
 *
 * Periodo estrecho (solo 2026-03-01, Evento2):
 *   total_attendances  = 2   (Alice, Carol)
 *   total_participants = 2   (Alice, Carol)
 *
 * Sin filtros (todos los tiempos):
 *   total_attendances  = 5   (Alice×3, Bob×1, Carol×1)
 *   total_participants = 3
 */
trait HasStatisticsScenario
{
    // ── Constantes esperadas ─────────────────────────────────────────

    protected const WIDE_DATE_FROM  = '2026-01-01';
    protected const WIDE_DATE_TO    = '2026-03-31';
    protected const NARROW_DATE     = '2026-03-01';

    protected const WIDE_ATTENDANCES  = 4;
    protected const WIDE_PARTICIPANTS = 3;
    protected const WIDE_EVENTS       = 2;

    protected const NARROW_ATTENDANCES  = 2;
    protected const NARROW_PARTICIPANTS = 2;

    protected const ALL_ATTENDANCES  = 5;
    protected const ALL_PARTICIPANTS = 3;

    // ── Constructor del escenario ─────────────────────────────────────

    protected function createScenario(): array
    {
        $progA = Program::factory()->create(['name' => 'Ingeniería de Sistemas']);
        $progB = Program::factory()->create(['name' => 'Administración de Empresas']);

        $admin = User::factory()->create(['role' => 'admin']);

        $event1   = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-01-10']);
        $event2   = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-03-01']);
        $eventOld = Event::factory()->create(['user_id' => $admin->id, 'date' => '2025-11-01']);

        $alice = Participant::factory()->create([
            'first_name'       => 'Alice',
            'last_name'        => 'García',
            'role'             => 'Estudiante',
            'sexo'             => 'F',
            'grupo_priorizado' => 'Víctimas',
            'program_id'       => $progA->id,
        ]);

        $bob = Participant::factory()->create([
            'first_name'       => 'Bob',
            'last_name'        => 'Martínez',
            'role'             => 'Docente',
            'sexo'             => 'M',
            'grupo_priorizado' => null,
            'program_id'       => $progB->id,
        ]);

        $carol = Participant::factory()->create([
            'first_name'       => 'Carol',
            'last_name'        => 'López',
            'role'             => 'Estudiante',
            'sexo'             => 'F',
            'grupo_priorizado' => 'LGBTQ+',
            'program_id'       => $progA->id,
        ]);

        // Alice asiste a los 3 eventos (2 dentro del periodo amplio + 1 fuera)
        Attendance::create(['event_id' => $event1->id,   'participant_id' => $alice->id]);
        Attendance::create(['event_id' => $event2->id,   'participant_id' => $alice->id]);
        Attendance::create(['event_id' => $eventOld->id, 'participant_id' => $alice->id]);

        // Bob solo asiste al evento 1
        Attendance::create(['event_id' => $event1->id, 'participant_id' => $bob->id]);

        // Carol solo asiste al evento 2
        Attendance::create(['event_id' => $event2->id, 'participant_id' => $carol->id]);

        return compact('admin', 'progA', 'progB', 'alice', 'bob', 'carol',
                       'event1', 'event2', 'eventOld');
    }

    protected function wideFilter(): array
    {
        return ['dateFrom' => self::WIDE_DATE_FROM, 'dateTo' => self::WIDE_DATE_TO];
    }

    protected function narrowFilter(): array
    {
        return ['dateFrom' => self::NARROW_DATE, 'dateTo' => self::NARROW_DATE];
    }
}
