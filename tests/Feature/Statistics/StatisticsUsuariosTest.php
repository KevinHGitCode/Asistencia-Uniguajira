<?php

namespace Tests\Feature\Statistics;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Statistics\Concerns\HasStatisticsScenario;
use Tests\TestCase;

/**
 * Módulo "Por Usuarios" — exclusivo para administradores
 *
 * Muestra la actividad de los usuarios del sistema:
 *   - Cuántos eventos ha creado cada usuario
 *   - Distribución de eventos por rol del usuario (admin vs user)
 *   - Qué usuarios acumularon más asistencias en sus eventos
 *
 * Endpoints verificados:
 *   GET /estadisticas/usuarios       (web — requiere role:admin)
 *   GET /api/statistics/total-events
 *   GET /api/statistics/events-by-role
 *   GET /api/statistics/events-by-user
 *   GET /api/statistics/top-users
 */
class StatisticsUsuariosTest extends TestCase
{
    use RefreshDatabase, HasStatisticsScenario;

    // ─────────────────────────────────────────────
    //  Control de acceso a la página web
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('statistics.usuarios'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.usuarios'))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_el_modulo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('statistics.usuarios'))
            ->assertOk();
    }

    public function test_pagina_usuarios_tiene_react_island_correcto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('statistics.usuarios'))
            ->assertSee('id="statistics-react-root"', false)
            ->assertSee('data-module="usuarios"', false);
    }

    // ─────────────────────────────────────────────
    //  total-events
    // ─────────────────────────────────────────────

    public function test_total_events_sin_filtros(): void
    {
        $this->createScenario(); // crea 3 eventos

        $this->getJson('/api/statistics/total-events')
            ->assertOk()
            ->assertJson(3);
    }

    public function test_total_events_con_filtro_de_fechas(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/total-events?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJson(self::WIDE_EVENTS); // 2 eventos dentro del periodo
    }

    public function test_total_events_cero_sin_datos(): void
    {
        $this->getJson('/api/statistics/total-events')
            ->assertOk()
            ->assertJson(0);
    }

    // ─────────────────────────────────────────────
    //  events-by-role  (rol del USUARIO que crea el evento)
    // ─────────────────────────────────────────────

    public function test_events_by_role_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/events-by-role')
            ->assertOk()
            ->assertJsonStructure([['role', 'count']]);
    }

    public function test_events_by_role_agrupa_por_rol_del_creador(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        // Admin crea 3 eventos, user crea 2 eventos
        Event::factory(3)->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        Event::factory(2)->create(['user_id' => $user->id,  'date' => '2026-02-01']);

        $response = $this->getJson('/api/statistics/events-by-role');
        $response->assertOk();

        $data = collect($response->json());

        $this->assertEquals(3, $data->firstWhere('role', 'admin')['count'] ?? 0);
        $this->assertEquals(2, $data->firstWhere('role', 'user')['count'] ?? 0);
    }

    public function test_events_by_role_retorna_ordenado_descendente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        Event::factory(5)->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        Event::factory(2)->create(['user_id' => $user->id,  'date' => '2026-02-01']);

        $response = $this->getJson('/api/statistics/events-by-role');
        $counts = collect($response->json())->pluck('count')->toArray();

        $sorted = collect($counts)->sortDesc()->values()->toArray();
        $this->assertEquals($sorted, $counts);
    }

    // ─────────────────────────────────────────────
    //  events-by-user
    // ─────────────────────────────────────────────

    public function test_events_by_user_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/events-by-user')
            ->assertOk()
            ->assertJsonStructure([['name', 'count']]);
    }

    public function test_events_by_user_asigna_correctamente_por_creador(): void
    {
        $admin  = User::factory()->create(['role' => 'admin', 'name' => 'Admin Principal']);
        $userA  = User::factory()->create(['role' => 'user',  'name' => 'Usuario Alfa']);

        Event::factory(4)->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        Event::factory(1)->create(['user_id' => $userA->id, 'date' => '2026-02-01']);

        $response = $this->getJson('/api/statistics/events-by-user?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data = collect($response->json());

        $this->assertEquals(4, $data->firstWhere('name', 'Admin Principal')['count'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('name', 'Usuario Alfa')['count'] ?? 0);
    }

    // ─────────────────────────────────────────────
    //  top-users
    // ─────────────────────────────────────────────

    public function test_top_users_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/top-users')
            ->assertOk()
            ->assertJsonStructure([['name', 'count']]);
    }

    public function test_top_users_mide_asistencias_en_sus_eventos(): void
    {
        // El "conteo" de top-users es el número de ASISTENCIAS
        // que recibieron los eventos del usuario, no los eventos creados.
        $admin  = User::factory()->create(['role' => 'admin', 'name' => 'Admin Mega']);
        $prog   = Program::factory()->create();

        // Admin crea un evento con 5 asistencias
        $event = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        for ($i = 0; $i < 5; $i++) {
            $part = Participant::factory()->create(['program_id' => $prog->id]);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $part->id]);
        }

        $response = $this->getJson('/api/statistics/top-users?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data = collect($response->json());
        $this->assertEquals(5, $data->firstWhere('name', 'Admin Mega')['count'] ?? 0);
    }

    public function test_top_users_retorna_maximo_5(): void
    {
        $prog = Program::factory()->create();

        // Crear 7 usuarios con eventos y asistencias
        for ($i = 1; $i <= 7; $i++) {
            $user  = User::factory()->create(['role' => 'admin']);
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            $part  = Participant::factory()->create(['program_id' => $prog->id]);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $part->id]);
        }

        $response = $this->getJson('/api/statistics/top-users');
        $response->assertOk();

        $this->assertLessThanOrEqual(5, count($response->json()));
    }

    public function test_top_users_ordenado_por_asistencias_descendente(): void
    {
        $prog  = Program::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin A']);
        $user  = User::factory()->create(['role' => 'user',  'name' => 'User B']);

        // Admin A tiene 3 asistencias en sus eventos
        $eventA = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-02-01']);
        for ($i = 0; $i < 3; $i++) {
            Attendance::create([
                'event_id'       => $eventA->id,
                'participant_id' => Participant::factory()->create(['program_id' => $prog->id])->id,
            ]);
        }

        // User B tiene 1 asistencia en sus eventos
        $eventB = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
        Attendance::create([
            'event_id'       => $eventB->id,
            'participant_id' => Participant::factory()->create(['program_id' => $prog->id])->id,
        ]);

        $response = $this->getJson('/api/statistics/top-users?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $counts = collect($response->json())->pluck('count')->toArray();
        $sorted = collect($counts)->sortDesc()->values()->toArray();

        $this->assertEquals($sorted, $counts);
        $this->assertEquals(3, $counts[0]); // Admin A primero
    }

    // ─────────────────────────────────────────────
    //  Filtros en módulo usuarios
    // ─────────────────────────────────────────────

    public function test_total_events_disminuye_con_rango_mas_estrecho(): void
    {
        $this->createScenario();

        $wide   = $this->getJson('/api/statistics/total-events?' . http_build_query($this->wideFilter()))->json();
        $narrow = $this->getJson('/api/statistics/total-events?' . http_build_query($this->narrowFilter()))->json();

        $this->assertGreaterThanOrEqual($narrow, $wide);
    }
}
