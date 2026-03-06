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
 * Módulo "Por Participantes"
 *
 * La diferencia CLAVE con el módulo de asistencias:
 *   → Cada PERSONA cuenta una sola vez, sin importar cuántos eventos haya marcado.
 *   → Todos los conteos usan COUNT(DISTINCT participants.id).
 *
 * Endpoints verificados:
 *   GET /api/statistics/total-participants
 *   GET /api/statistics/participants-by-program
 *   GET /api/statistics/participants-by-role
 *   GET /api/statistics/participants-by-sex
 *   GET /api/statistics/participants-by-group
 */
class StatisticsParticipantesTest extends TestCase
{
    use RefreshDatabase, HasStatisticsScenario;

    // ─────────────────────────────────────────────
    //  total-participants
    // ─────────────────────────────────────────────

    public function test_total_participants_sin_filtros(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/total-participants')
            ->assertOk()
            ->assertJson(self::ALL_PARTICIPANTS);
    }

    public function test_total_participants_con_filtro_amplio(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/total-participants?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJson(self::WIDE_PARTICIPANTS);
    }

    public function test_total_participants_con_filtro_estricto(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/total-participants?' . http_build_query($this->narrowFilter()))
            ->assertOk()
            ->assertJson(self::NARROW_PARTICIPANTS);
    }

    public function test_total_participants_cero_sin_datos(): void
    {
        $this->getJson('/api/statistics/total-participants')
            ->assertOk()
            ->assertJson(0);
    }

    public function test_participante_sin_asistencias_no_cuenta(): void
    {
        // Crear un participante que NUNCA asistió
        $prog = Program::factory()->create();
        Participant::factory()->create(['program_id' => $prog->id]);

        $this->getJson('/api/statistics/total-participants')
            ->assertOk()
            ->assertJson(0);
    }

    public function test_misma_persona_multiples_eventos_cuenta_una_sola_vez(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $prog = Program::factory()->create();

        $alice = Participant::factory()->create(['program_id' => $prog->id]);

        // Alice asiste a 5 eventos distintos
        for ($i = 0; $i < 5; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $alice->id]);
        }

        $this->getJson('/api/statistics/total-participants?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJson(1); // Solo Alice — cuenta una vez
    }

    // ─────────────────────────────────────────────
    //  participants-by-program  (COUNT DISTINCT)
    // ─────────────────────────────────────────────

    public function test_participants_by_program_retorna_array(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-program?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonIsArray();
    }

    public function test_participants_by_program_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-program?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['program', 'count']]);
    }

    public function test_participants_by_program_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-program?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data     = collect($response->json());
        $ingCount = $data->firstWhere('program', 'Ingeniería de Sistemas')['count'] ?? 0;
        $admCount = $data->firstWhere('program', 'Administración de Empresas')['count'] ?? 0;

        // Alice asistió ×2 pero es UNA persona en Ingeniería
        // Carol asistió ×1 en Ingeniería
        // → Ingeniería = 2 personas únicas (no 3 asistencias)
        $this->assertEquals(2, $ingCount);
        $this->assertEquals(1, $admCount);
    }

    public function test_participants_by_program_suma_no_supera_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-program?' . http_build_query($this->wideFilter()));
        $suma     = collect($response->json())->sum('count');

        // La suma puede ser igual (cuando cada persona pertenece a un solo programa)
        // pero nunca puede superar el total de participantes únicos
        $this->assertLessThanOrEqual(self::WIDE_PARTICIPANTS, $suma);
    }

    // ─────────────────────────────────────────────
    //  participants-by-role  (COUNT DISTINCT)
    // ─────────────────────────────────────────────

    public function test_participants_by_role_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-role?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_participants_by_role_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-role?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // 2 Estudiantes únicas (Alice + Carol), 1 Docente (Bob)
        $this->assertEquals(2, $data->firstWhere('label', 'Estudiante')['count'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('label', 'Docente')['count'] ?? 0);
    }

    public function test_participants_by_role_suma_coincide_con_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-role?' . http_build_query($this->wideFilter()));
        $suma     = collect($response->json())->sum('count');

        $this->assertEquals(self::WIDE_PARTICIPANTS, $suma);
    }

    // ─────────────────────────────────────────────
    //  participants-by-sex  (COUNT DISTINCT)
    // ─────────────────────────────────────────────

    public function test_participants_by_sex_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-sex?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_participants_by_sex_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-sex?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // 2 mujeres únicas (Alice + Carol), 1 hombre (Bob)
        $this->assertEquals(2, $data->firstWhere('label', 'F')['count'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('label', 'M')['count'] ?? 0);
    }

    public function test_participants_by_sex_usa_sin_datos_para_nulos(): void
    {
        $user  = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);

        $sinSexo = Participant::factory()->create(['sexo' => null, 'program_id' => $prog->id]);
        Attendance::create(['event_id' => $event->id, 'participant_id' => $sinSexo->id]);

        $response = $this->getJson('/api/statistics/participants-by-sex');
        $data     = collect($response->json());

        $this->assertNotNull($data->firstWhere('label', 'Sin datos'));
    }

    // ─────────────────────────────────────────────
    //  participants-by-group  (COUNT DISTINCT)
    // ─────────────────────────────────────────────

    public function test_participants_by_group_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['label', 'count']]);
    }

    public function test_participants_by_group_cuenta_personas_unicas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Alice (Víctimas) asistió ×2 pero cuenta como 1 persona
        $victimas = $data->firstWhere('label', 'Víctimas');
        $this->assertNotNull($victimas);
        $this->assertEquals(1, $victimas['count']);
    }

    public function test_participants_by_group_usa_sin_datos_para_nulos(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Bob tiene grupo_priorizado null
        $sinDatos = $data->firstWhere('label', 'Sin datos');
        $this->assertNotNull($sinDatos);
        $this->assertEquals(1, $sinDatos['count']);
    }

    public function test_participants_by_group_suma_coincide_con_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $suma     = collect($response->json())->sum('count');

        $this->assertEquals(self::WIDE_PARTICIPANTS, $suma);
    }
}
