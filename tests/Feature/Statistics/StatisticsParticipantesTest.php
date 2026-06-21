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

        $response = $this->getJson('/api/statistics/total-participants');
        $response->assertOk();
        $this->assertEquals(self::ALL_PARTICIPANTS, $response->json());
    }

    public function test_total_participants_con_filtro_amplio(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/total-participants?' . http_build_query($this->wideFilter()));
        $response->assertOk();
        $this->assertEquals(self::WIDE_PARTICIPANTS, $response->json());
    }

    public function test_total_participants_con_filtro_estricto(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/total-participants?' . http_build_query($this->narrowFilter()));
        $response->assertOk();
        $this->assertEquals(self::NARROW_PARTICIPANTS, $response->json());
    }

    public function test_total_participants_cero_sin_datos(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->getJson('/api/statistics/total-participants');
        $response->assertOk();
        $this->assertEquals(0, $response->json());
    }

    public function test_participante_sin_asistencias_no_cuenta(): void
    {
        // Crear un participante que NUNCA asistió
        $prog = Program::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $participant = Participant::create([
            'document' => 'STAT-PART-NO-ATT',
            'first_name' => 'Sin',
            'last_name' => 'Asistencia',
            'email' => 'participante.sin.asistencia@example.com',
        ]);
        $this->participantRole($participant, 'Estudiante', $prog);

        $response = $this->getJson('/api/statistics/total-participants');
        $response->assertOk();
        $this->assertEquals(0, $response->json());
    }

    public function test_misma_persona_multiples_eventos_cuenta_una_sola_vez(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $prog = Program::factory()->create();
        $this->actingAs($user);

        $alice = Participant::create([
            'document' => 'STAT-PART-ALICE',
            'first_name' => 'Alice',
            'last_name' => 'Unica',
            'email' => 'alice.unica@example.com',
        ]);
        $role = $this->participantRole($alice, 'Estudiante', $prog);

        // Alice asiste a 5 eventos distintos
        for ($i = 0; $i < 5; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            $this->attendanceWithDetails($event, $alice, $role, 'F');
        }

        $response = $this->getJson('/api/statistics/total-participants?' . http_build_query($this->wideFilter()));
        $response->assertOk();
        $this->assertEquals(1, $response->json()); // Solo Alice — cuenta una vez
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
            ->assertJsonStructure([['name', 'value']]);
    }

    public function test_participants_by_program_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-program?' . http_build_query($this->wideFilter()));
        $response->assertOk();

        $data     = collect($response->json());
        $ingCount = $data->firstWhere('name', 'Ingenieria de Sistemas')['value'] ?? 0;
        $admCount = $data->firstWhere('name', 'Administracion de Empresas')['value'] ?? 0;

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
        $suma     = collect($response->json())->sum('value');

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
            ->assertJsonStructure([['name', 'value']]);
    }

    public function test_participants_by_role_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-role?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // 2 Estudiantes únicas (Alice + Carol), 1 Docente (Bob)
        $this->assertEquals(2, $data->firstWhere('name', 'Estudiante')['value'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('name', 'Docente')['value'] ?? 0);
    }

    public function test_participants_by_role_suma_coincide_con_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-role?' . http_build_query($this->wideFilter()));
        $suma     = collect($response->json())->sum('value');

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
            ->assertJsonStructure([['name', 'value']]);
    }

    public function test_participants_by_sex_usa_count_distinct(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-sex?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // 2 mujeres únicas (Alice + Carol), 1 hombre (Bob)
        $this->assertEquals(2, $data->firstWhere('name', 'F')['value'] ?? 0);
        $this->assertEquals(1, $data->firstWhere('name', 'M')['value'] ?? 0);
    }

    public function test_participants_by_sex_usa_sin_datos_para_nulos(): void
    {
        $user  = User::factory()->create(['role' => 'admin']);
        $prog  = Program::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
        $this->actingAs($user);

        $sinSexo = Participant::create([
            'document' => 'STAT-PART-NULL-SEX',
            'first_name' => 'Sin',
            'last_name' => 'Sexo',
            'email' => 'participante.sin.sexo@example.com',
        ]);
        $role = $this->participantRole($sinSexo, 'Estudiante', $prog);
        $this->attendanceWithDetails($event, $sinSexo, $role, null);

        $response = $this->getJson('/api/statistics/participants-by-sex');
        $data     = collect($response->json());

        $this->assertNotNull($data->firstWhere('name', 'Sin datos'));
    }

    // ─────────────────────────────────────────────
    //  participants-by-group  (COUNT DISTINCT)
    // ─────────────────────────────────────────────

    public function test_participants_by_group_estructura_correcta(): void
    {
        $this->createScenario();

        $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()))
            ->assertOk()
            ->assertJsonStructure([['name', 'value']]);
    }

    public function test_participants_by_group_cuenta_personas_unicas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Alice (Víctimas) asistió ×2 pero cuenta como 1 persona
        $victimas = $data->firstWhere('name', 'Victimas');
        $this->assertNotNull($victimas);
        $this->assertEquals(1, $victimas['value']);
    }

    public function test_participants_by_group_usa_sin_datos_para_nulos(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $data     = collect($response->json());

        // Bob tiene grupo_priorizado null
        $sinDatos = $data->firstWhere('name', 'Sin datos');
        $this->assertNotNull($sinDatos);
        $this->assertEquals(1, $sinDatos['value']);
    }

    public function test_participants_by_group_suma_coincide_con_total(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/participants-by-group?' . http_build_query($this->wideFilter()));
        $suma     = collect($response->json())->sum('value');

        $this->assertEquals(self::WIDE_PARTICIPANTS, $suma);
    }
}
