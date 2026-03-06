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
 * Módulo "Por Asistencias"
 *
 * En este módulo CADA REGISTRO DE ASISTENCIA cuenta independientemente.
 * Si Alice asistió a 3 eventos, suma 3 en todos los conteos de asistencia.
 *
 * Endpoints verificados:
 *   GET /api/statistics/total-attendances
 *   GET /api/statistics/attendances-by-program
 *   GET /api/statistics/attendances-by-role
 *   GET /api/statistics/attendances-by-sex
 *   GET /api/statistics/attendances-by-group
 *   GET /api/statistics/attendances-over-time
 *   GET /api/statistics/top-events
 *   GET /api/statistics/top-participants
 */
class StatisticsAsistenciasTest extends TestCase
{
    use RefreshDatabase, HasStatisticsScenario;

    // ─────────────────────────────────────────────
    //  total-attendances
    // ─────────────────────────────────────────────

    public function test_total_attendances_sin_filtros(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/total-attendances');
        $response->assertOk();
        $this->assertEquals(self::ALL_ATTENDANCES, $response->json());
    }

    public function test_total_attendances_con_filtro_de_fechas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/total-attendances?' . http_build_query($this->wideFilter()));
        $response->assertOk();
        $this->assertEquals(self::WIDE_ATTENDANCES, $response->json());
    }

    public function test_total_attendances_con_filtro_estricto(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/total-attendances?' . http_build_query($this->narrowFilter()));
        $response->assertOk();
        $this->assertEquals(self::NARROW_ATTENDANCES, $response->json());
    }

    public function test_total_attendances_cero_sin_datos(): void
    {
        // No se crea ningún dato
        $response = $this->getJson('/api/statistics/total-attendances');
        $response->assertOk();
        $this->assertSame(0, $response->json());
    }

    // ─────────────────────────────────────────────
    //  attendances-by-program  (cuenta registros, no personas únicas)
    // ─────────────────────────────────────────────

    public function test_attendances_by_program_retorna_array(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/attendances-by-program?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonIsArray();
    }

    public function test_attendances_by_program_estructura_de_items(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/attendances-by-program?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['program', 'count']]);
    }

    public function test_attendances_by_program_cuenta_multiple_asistencias_de_la_misma_persona(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-program?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data     = collect($response->json());
        $ingCount = $data->firstWhere('program', 'Ingeniería de Sistemas')['count'] ?? 0;

        // Alice (Ing) asistió 2 veces + Carol (Ing) 1 vez = 3
        $this->assertEquals(3, $ingCount);
    }

    public function test_attendances_by_program_suma_coincide_con_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-program?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $suma = collect($response->json())->sum('count');

        $this->assertEquals(self::WIDE_ATTENDANCES, $suma);
    }

    public function test_attendances_by_program_ordenado_descendente(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-program?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $counts = collect($response->json())->pluck('count')->toArray();
        $sorted = collect($counts)->sortDesc()->values()->toArray();

        $this->assertEquals($sorted, $counts);
    }

    // ─────────────────────────────────────────────
    //  attendances-by-role  (por estamento del participante)
    // ─────────────────────────────────────────────

    public function test_attendances_by_role_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/attendances-by-role?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_attendances_by_role_cuenta_registros_no_personas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-role?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data         = collect($response->json());
        $estudCount   = $data->firstWhere('label', 'Estudiante')['count'] ?? 0;
        $docenteCount = $data->firstWhere('label', 'Docente')['count'] ?? 0;

        // Alice (Estudiante) ×2 + Carol (Estudiante) ×1 = 3
        $this->assertEquals(3, $estudCount);
        // Bob (Docente) ×1 = 1
        $this->assertEquals(1, $docenteCount);
    }

    public function test_attendances_by_role_suma_igual_a_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-role?' . http_build_query($this->wideFilter()));

        $suma = collect($response->json())->sum('count');

        $this->assertEquals(self::WIDE_ATTENDANCES, $suma);
    }

    // ─────────────────────────────────────────────
    //  attendances-by-sex
    // ─────────────────────────────────────────────

    public function test_attendances_by_sex_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/attendances-by-sex?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_attendances_by_sex_cuenta_registros_no_personas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-sex?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // F: Alice×2 + Carol×1 = 3; M: Bob×1 = 1
        $this->assertEquals(3, $data->firstWhere('label', 'F')['count'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('label', 'M')['count'] ?? 0);
    }

    public function test_attendances_by_sex_usa_sin_datos_para_nulos(): void
    {
        $user  = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);

        $sinSexo = Participant::factory()->create(['sexo' => null, 'program_id' => $prog->id]);
        Attendance::create(['event_id' => $event->id, 'participant_id' => $sinSexo->id]);

        $response = $this->getJson('/api/statistics/attendances-by-sex');
        $data     = collect($response->json());

        $this->assertNotNull($data->firstWhere('label', 'Sin datos'));
    }

    // ─────────────────────────────────────────────
    //  attendances-by-group
    // ─────────────────────────────────────────────

    public function test_attendances_by_group_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/attendances-by-group?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_attendances_by_group_usa_sin_datos_para_nulos(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Bob tiene grupo_priorizado null → debe aparecer como "Sin datos"
        $sinDatos = $data->firstWhere('label', 'Sin datos');
        $this->assertNotNull($sinDatos);
        $this->assertEquals(1, $sinDatos['count']); // Bob asistió 1 vez en el periodo
    }

    public function test_attendances_by_group_cuenta_multiples_asistencias(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Alice (Víctimas) asistió ×2 en el periodo
        $victimas = $data->firstWhere('label', 'Víctimas');
        $this->assertNotNull($victimas);
        $this->assertEquals(2, $victimas['count']);
    }

    // ─────────────────────────────────────────────
    //  top-events
    // ─────────────────────────────────────────────

    public function test_top_events_retorna_maximo_5_items(): void
    {
        $user  = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();

        // Crear 7 eventos con asistencias
        for ($i = 1; $i <= 7; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            $part  = Participant::factory()->create(['program_id' => $prog->id]);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $part->id]);
        }

        $response = $this->getJson('/api/statistics/top-events');
        $response->assertOk();

        $this->assertLessThanOrEqual(5, count($response->json()));
    }

    public function test_top_events_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/top-events')
            ->assertOk()
            ->assertJsonStructure([['title', 'count']]);
    }

    // ─────────────────────────────────────────────
    //  top-participants
    //  NOTA: topParticipants() usa CONCAT() que no es compatible con SQLite.
    // ─────────────────────────────────────────────

    public function test_top_participants_retorna_maximo_5_items(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('top-participants usa CONCAT() incompatible con SQLite.');
        }

        $user = User::factory()->create(['role' => 'admin']);
        $prog = Program::factory()->create();

        // 7 participantes cada uno con 1 asistencia
        for ($i = 0; $i < 7; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            $part  = Participant::factory()->create(['program_id' => $prog->id]);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $part->id]);
        }

        $response = $this->getJson('/api/statistics/top-participants');
        $response->assertOk();

        $this->assertLessThanOrEqual(5, count($response->json()));
    }

    public function test_top_participants_estructura_correcta(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('top-participants usa CONCAT() incompatible con SQLite.');
        }

        $this->createScenario();

        $this->getJson('/api/statistics/top-participants')
            ->assertOk()
            ->assertJsonStructure([['name', 'count']]);
    }

    /**
     * NOTA: topParticipants() NO aplica filtros de fecha — comportamiento conocido.
     * Aunque se envíe dateFrom/dateTo, devuelve todos los participantes históricos.
     */
    public function test_top_participants_ignora_filtros_de_fecha(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('top-participants usa CONCAT() incompatible con SQLite.');
        }

        $this->createScenario();

        // El evento fuera del periodo amplio también se incluye
        $responseConFiltro = $this->getJson('/api/statistics/top-participants?' . http_build_query($this->wideFilter()));
        $responseSinFiltro = $this->getJson('/api/statistics/top-participants');

        // Ambas respuestas deben ser iguales porque el endpoint ignora los filtros
        $this->assertEquals($responseSinFiltro->json(), $responseConFiltro->json());
    }
}
