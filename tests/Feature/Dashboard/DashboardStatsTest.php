<?php

namespace Tests\Feature\Dashboard;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Estadísticas generales del dashboard
 *
 * Reglas de negocio:
 *   - Admin → conteos GLOBALES (todos los eventos, asistencias, participantes del sistema)
 *   - Usuario → conteos propios (solo sus eventos y asistencias)
 *   - participantesCount usa COUNT(DISTINCT participant_id): una persona
 *     que asiste múltiples veces cuenta como 1
 *   - Participantes sin ninguna asistencia NO se cuentan
 *
 * Variables pasadas a la vista:
 *   username, eventosCount, asistenciasCount, participantesCount
 */
class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Variables de la vista
    // ─────────────────────────────────────────────

    public function test_vista_recibe_todas_las_variables_requeridas(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHasAll([
                'username',
                'eventosCount',
                'asistenciasCount',
                'participantesCount',
            ]);
    }

    // ─────────────────────────────────────────────
    //  Admin — conteos globales
    // ─────────────────────────────────────────────

    public function test_admin_ve_eventos_de_todos_los_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otro  = User::factory()->create(['role' => 'user']);

        // Admin crea 2 eventos, otro usuario crea 3 → total = 5
        Event::factory(2)->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        Event::factory(3)->create(['user_id' => $otro->id,  'date' => '2026-02-01']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertViewHas('eventosCount', 5);
    }

    public function test_admin_ve_asistencias_de_todos_los_eventos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otro  = User::factory()->create(['role' => 'user']);
        $prog  = Program::factory()->create();

        $eventoAdmin = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        $eventoOtro  = Event::factory()->create(['user_id' => $otro->id,  'date' => '2026-02-01']);

        $p = Participant::factory()->create(['program_id' => $prog->id]);
        Attendance::create(['event_id' => $eventoAdmin->id, 'participant_id' => $p->id]);
        Attendance::create(['event_id' => $eventoOtro->id,  'participant_id' => $p->id]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertViewHas('asistenciasCount', 2);
    }

    public function test_admin_cuenta_solo_participantes_con_al_menos_una_asistencia(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();
        $event = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);

        $p1 = Participant::factory()->create(['program_id' => $prog->id]);
        $p2 = Participant::factory()->create(['program_id' => $prog->id]);
        Participant::factory()->create(['program_id' => $prog->id]); // sin asistencia → no cuenta

        Attendance::create(['event_id' => $event->id, 'participant_id' => $p1->id]);
        Attendance::create(['event_id' => $event->id, 'participant_id' => $p2->id]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertViewHas('participantesCount', 2);
    }

    // ─────────────────────────────────────────────
    //  Usuario — conteos propios
    // ─────────────────────────────────────────────

    public function test_usuario_ve_solo_sus_propios_eventos(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        Event::factory(2)->create(['user_id' => $user->id,  'date' => '2026-02-01']);
        Event::factory(5)->create(['user_id' => $admin->id, 'date' => '2026-02-01']); // no debe contar

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('eventosCount', 2);
    }

    public function test_usuario_ve_solo_asistencias_de_sus_eventos(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();

        $miEvento   = Event::factory()->create(['user_id' => $user->id,  'date' => '2026-02-01']);
        $otroEvento = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);

        $p1 = Participant::factory()->create(['program_id' => $prog->id]);
        $p2 = Participant::factory()->create(['program_id' => $prog->id]);
        $p3 = Participant::factory()->create(['program_id' => $prog->id]);

        // 2 asistencias a MI evento, 1 al evento ajeno → debe ver 2
        Attendance::create(['event_id' => $miEvento->id,   'participant_id' => $p1->id]);
        Attendance::create(['event_id' => $miEvento->id,   'participant_id' => $p2->id]);
        Attendance::create(['event_id' => $otroEvento->id, 'participant_id' => $p3->id]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('asistenciasCount', 2);
    }

    public function test_usuario_ve_participantes_unicos_de_sus_eventos(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();

        $miEvento   = Event::factory()->create(['user_id' => $user->id,  'date' => '2026-02-01']);
        $otroEvento = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);

        $alice = Participant::factory()->create(['program_id' => $prog->id]);
        $bob   = Participant::factory()->create(['program_id' => $prog->id]);

        // Alice asiste a MI evento, Bob asiste al evento ajeno → debe ver 1
        Attendance::create(['event_id' => $miEvento->id,   'participant_id' => $alice->id]);
        Attendance::create(['event_id' => $otroEvento->id, 'participant_id' => $bob->id]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('participantesCount', 1);
    }

    public function test_usuario_participante_repetido_cuenta_una_sola_vez(): void
    {
        // Alice asiste a 3 eventos del mismo usuario → cuenta como 1 participante
        $user = User::factory()->create(['role' => 'user']);
        $prog = Program::factory()->create();
        $alice = Participant::factory()->create(['program_id' => $prog->id]);

        for ($i = 0; $i < 3; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $alice->id]);
        }

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('participantesCount', 1);
    }

    // ─────────────────────────────────────────────
    //  Sin datos — conteos en cero
    // ─────────────────────────────────────────────

    public function test_admin_sin_datos_ve_todos_los_conteos_en_cero(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertViewHas('eventosCount', 0)
            ->assertViewHas('asistenciasCount', 0)
            ->assertViewHas('participantesCount', 0);
    }

    public function test_usuario_sin_eventos_propios_ve_ceros_aunque_existan_eventos_ajenos(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Hay datos en el sistema, pero no del usuario logueado
        Event::factory(3)->create(['user_id' => $admin->id, 'date' => '2026-02-01']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('eventosCount', 0)
            ->assertViewHas('asistenciasCount', 0)
            ->assertViewHas('participantesCount', 0);
    }
}
