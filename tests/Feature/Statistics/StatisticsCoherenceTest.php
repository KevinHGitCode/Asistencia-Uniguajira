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
 * Coherencia entre módulos de estadísticas
 *
 * Invariantes que SIEMPRE deben cumplirse:
 *
 *   1. total_attendances >= total_participants  (para cualquier filtro)
 *   2. suma(attendances_by_program) == total_attendances
 *   3. suma(participants_by_X) == total_participants
 *   4. Al reducir el rango de fechas, los conteos disminuyen (o se mantienen)
 *   5. Una persona con N asistencias → suma N en asistencias, suma 1 en participantes
 *   6. Un participante sin asistencias NO aparece en ningún conteo
 */
class StatisticsCoherenceTest extends TestCase
{
    use RefreshDatabase, HasStatisticsScenario;

    // ─────────────────────────────────────────────
    //  Invariante 1: asistencias >= participantes
    // ─────────────────────────────────────────────

    public function test_total_attendances_es_mayor_o_igual_que_total_participants(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $attendances  = $this->getJson("/api/statistics/total-attendances?$q")->json();
        $participants = $this->getJson("/api/statistics/total-participants?$q")->json();

        $this->assertGreaterThanOrEqual($participants, $attendances);
    }

    public function test_invariante_asistencias_vs_participantes_con_filtro_estricto(): void
    {
        $this->createScenario();
        $q = http_build_query($this->narrowFilter());

        $attendances  = $this->getJson("/api/statistics/total-attendances?$q")->json();
        $participants = $this->getJson("/api/statistics/total-participants?$q")->json();

        $this->assertGreaterThanOrEqual($participants, $attendances);
    }

    public function test_invariante_asistencias_vs_participantes_sin_filtros(): void
    {
        $this->createScenario();

        $attendances  = $this->getJson('/api/statistics/total-attendances')->json();
        $participants = $this->getJson('/api/statistics/total-participants')->json();

        $this->assertGreaterThanOrEqual($participants, $attendances);
    }

    // ─────────────────────────────────────────────
    //  Invariante 2: suma(by_program) == total
    // ─────────────────────────────────────────────

    public function test_suma_attendances_by_program_coincide_con_total(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $total = $this->getJson("/api/statistics/total-attendances?$q")->json();
        $suma  = collect($this->getJson("/api/statistics/attendances-by-program?$q")->json())->sum('count');

        $this->assertEquals($total, $suma);
    }

    public function test_suma_participants_by_program_coincide_con_total(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $total = $this->getJson("/api/statistics/total-participants?$q")->json();
        $suma  = collect($this->getJson("/api/statistics/participants-by-program?$q")->json())->sum('count');

        $this->assertEquals($total, $suma);
    }

    public function test_suma_participants_by_role_coincide_con_total(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $total = $this->getJson("/api/statistics/total-participants?$q")->json();
        $suma  = collect($this->getJson("/api/statistics/participants-by-role?$q")->json())->sum('count');

        $this->assertEquals($total, $suma);
    }

    public function test_suma_participants_by_sex_coincide_con_total(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $total = $this->getJson("/api/statistics/total-participants?$q")->json();
        $suma  = collect($this->getJson("/api/statistics/participants-by-sex?$q")->json())->sum('count');

        $this->assertEquals($total, $suma);
    }

    public function test_suma_participants_by_group_coincide_con_total(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $total = $this->getJson("/api/statistics/total-participants?$q")->json();
        $suma  = collect($this->getJson("/api/statistics/participants-by-group?$q")->json())->sum('count');

        $this->assertEquals($total, $suma);
    }

    // ─────────────────────────────────────────────
    //  Invariante 5: misma persona — cuenta diferente según el módulo
    // ─────────────────────────────────────────────

    public function test_misma_persona_suma_n_en_asistencias_y_1_en_participantes(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $prog = Program::factory()->create(['name' => 'Programa Test']);

        $alice = Participant::factory()->create([
            'role'       => 'Estudiante',
            'program_id' => $prog->id,
        ]);

        // Alice asiste a 4 eventos distintos dentro del periodo
        for ($i = 0; $i < 4; $i++) {
            $event = Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-01']);
            Attendance::create(['event_id' => $event->id, 'participant_id' => $alice->id]);
        }

        $q = http_build_query($this->wideFilter());

        $totalAttendances  = $this->getJson("/api/statistics/total-attendances?$q")->json();
        $totalParticipants = $this->getJson("/api/statistics/total-participants?$q")->json();

        $this->assertEquals(4, $totalAttendances);  // 4 registros de asistencia
        $this->assertEquals(1, $totalParticipants); // 1 persona única

        // En by-program también se ve la diferencia
        $byProgAtt  = collect($this->getJson("/api/statistics/attendances-by-program?$q")->json());
        $byProgPart = collect($this->getJson("/api/statistics/participants-by-program?$q")->json());

        $this->assertEquals(4, $byProgAtt->firstWhere('program', 'Programa Test')['count'] ?? 0);
        $this->assertEquals(1, $byProgPart->firstWhere('program', 'Programa Test')['count'] ?? 0);
    }

    // ─────────────────────────────────────────────
    //  Invariante 6: participante sin asistencias no aparece en ningún conteo
    // ─────────────────────────────────────────────

    public function test_participante_sin_asistencias_no_aparece_en_ningun_conteo(): void
    {
        $prog = Program::factory()->create(['name' => 'Programa X']);
        Participant::factory()->create(['role' => 'Estudiante', 'program_id' => $prog->id]);

        $q = http_build_query($this->wideFilter());

        // Total
        $this->assertEquals(0, $this->getJson("/api/statistics/total-participants?$q")->json());

        // by-program: no debe aparecer el programa
        $byProg = collect($this->getJson("/api/statistics/participants-by-program?$q")->json());
        $this->assertNull($byProg->firstWhere('program', 'Programa X'));

        // by-role: suma debe ser 0
        $sumaRole = collect($this->getJson("/api/statistics/participants-by-role?$q")->json())->sum('count');
        $this->assertEquals(0, $sumaRole);
    }

    // ─────────────────────────────────────────────
    //  Diferencia numérica entre ambos módulos
    // ─────────────────────────────────────────────

    public function test_by_program_asistencias_mayor_que_participantes_cuando_hay_repeticion(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $attByProg  = collect($this->getJson("/api/statistics/attendances-by-program?$q")->json());
        $partByProg = collect($this->getJson("/api/statistics/participants-by-program?$q")->json());

        // En Ingeniería: 3 asistencias vs 2 participantes únicos
        $ingAtt  = $attByProg->firstWhere('program', 'Ingeniería de Sistemas')['count'] ?? 0;
        $ingPart = $partByProg->firstWhere('program', 'Ingeniería de Sistemas')['count'] ?? 0;

        $this->assertGreaterThan($ingPart, $ingAtt);
    }
}
