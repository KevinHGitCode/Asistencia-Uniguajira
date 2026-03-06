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
 * Comportamiento de los filtros de fecha (dateFrom / dateTo)
 *
 * Reglas:
 *   1. dateFrom excluye eventos anteriores a la fecha
 *   2. dateTo excluye eventos posteriores a la fecha
 *   3. Los eventos EN la fecha límite SÍ se incluyen (límites inclusivos)
 *   4. Reducir el rango → datos disminuyen o se mantienen (nunca aumentan)
 *   5. Sin filtros → retorna todos los datos históricos
 *   6. Rango sin datos → retorna cero / array vacío
 */
class StatisticsFiltersTest extends TestCase
{
    use RefreshDatabase, HasStatisticsScenario;

    // ─────────────────────────────────────────────
    //  dateFrom excluye eventos anteriores
    // ─────────────────────────────────────────────

    public function test_dateFrom_excluye_eventos_anteriores(): void
    {
        $this->createScenario();

        // Con dateFrom=2026-01-01 solo se incluyen los eventos 1 y 2
        // El eventOld (2025-11-01) queda fuera
        $response = $this->getJson('/api/statistics/total-attendances?dateFrom=' . self::WIDE_DATE_FROM);
        $response->assertOk();

        // Alice solo tiene 2 asistencias dentro del periodo + Bob y Carol con 1 c/u = 4
        $this->assertEquals(self::WIDE_ATTENDANCES, $response->json());
    }

    public function test_dateFrom_sin_dateTo_incluye_todo_lo_posterior(): void
    {
        $this->createScenario();

        // Solo dateFrom: incluye desde 2026-01-01 hasta el presente (y futuro)
        $response = $this->getJson('/api/statistics/total-attendances?dateFrom=' . self::WIDE_DATE_FROM);
        $this->assertEquals(self::WIDE_ATTENDANCES, $response->json());
    }

    // ─────────────────────────────────────────────
    //  dateTo excluye eventos posteriores
    // ─────────────────────────────────────────────

    public function test_dateTo_excluye_eventos_posteriores(): void
    {
        $this->createScenario();

        // Solo hasta 2026-01-31: entran eventOld (2025-11-01) y Evento1 (2026-01-10)
        $response = $this->getJson('/api/statistics/total-attendances?dateTo=2026-01-31');
        $response->assertOk();

        // Alice asistió a eventOld + event1 = 2; Bob asistió a event1 = 1 → total 3
        $this->assertEquals(3, $response->json());
    }

    public function test_dateTo_sin_dateFrom_incluye_todo_lo_anterior(): void
    {
        $this->createScenario();

        // Solo dateTo: incluye desde el comienzo de los datos hasta esa fecha
        $response = $this->getJson('/api/statistics/total-attendances?dateTo=' . self::WIDE_DATE_TO);

        // Incluye eventOld + event1 + event2 = 5 asistencias
        $this->assertEquals(self::ALL_ATTENDANCES, $response->json());
    }

    // ─────────────────────────────────────────────
    //  Límites inclusivos
    // ─────────────────────────────────────────────

    public function test_dateFrom_y_dateTo_son_inclusivos(): void
    {
        $this->createScenario();

        // Evento2 es exactamente el 2026-03-01
        // Con dateFrom=dateTo=2026-03-01, debe incluir ese evento
        $response = $this->getJson('/api/statistics/total-attendances?' . http_build_query($this->narrowFilter()));
        $response->assertOk();

        $this->assertEquals(self::NARROW_ATTENDANCES, $response->json()); // Alice + Carol
    }

    // ─────────────────────────────────────────────
    //  Rango amplio >= rango estrecho
    // ─────────────────────────────────────────────

    public function test_rango_amplio_retorna_igual_o_mas_asistencias(): void
    {
        $this->createScenario();

        $wide   = $this->getJson('/api/statistics/total-attendances?' . http_build_query($this->wideFilter()))->json();
        $narrow = $this->getJson('/api/statistics/total-attendances?' . http_build_query($this->narrowFilter()))->json();

        $this->assertGreaterThanOrEqual($narrow, $wide);
    }

    public function test_rango_amplio_retorna_igual_o_mas_participantes(): void
    {
        $this->createScenario();

        $wide   = $this->getJson('/api/statistics/total-participants?' . http_build_query($this->wideFilter()))->json();
        $narrow = $this->getJson('/api/statistics/total-participants?' . http_build_query($this->narrowFilter()))->json();

        $this->assertGreaterThanOrEqual($narrow, $wide);
    }

    public function test_rango_amplio_retorna_igual_o_mas_eventos(): void
    {
        $this->createScenario();

        $wide   = $this->getJson('/api/statistics/total-events?' . http_build_query($this->wideFilter()))->json();
        $narrow = $this->getJson('/api/statistics/total-events?' . http_build_query($this->narrowFilter()))->json();

        $this->assertGreaterThanOrEqual($narrow, $wide);
    }

    // ─────────────────────────────────────────────
    //  Sin filtros = datos históricos completos
    // ─────────────────────────────────────────────

    public function test_sin_filtros_retorna_todos_los_datos(): void
    {
        $this->createScenario();

        $attResponse  = $this->getJson('/api/statistics/total-attendances');
        $partResponse = $this->getJson('/api/statistics/total-participants');

        $this->assertEquals(self::ALL_ATTENDANCES,  $attResponse->json());
        $this->assertEquals(self::ALL_PARTICIPANTS, $partResponse->json());
    }

    // ─────────────────────────────────────────────
    //  Rango sin datos
    // ─────────────────────────────────────────────

    public function test_rango_sin_datos_retorna_cero(): void
    {
        $this->createScenario();

        // Rango en el futuro lejano donde no hay eventos
        $attResponse  = $this->getJson('/api/statistics/total-attendances?dateFrom=2099-01-01&dateTo=2099-12-31');
        $partResponse = $this->getJson('/api/statistics/total-participants?dateFrom=2099-01-01&dateTo=2099-12-31');

        $this->assertEquals(0, $attResponse->json());
        $this->assertEquals(0, $partResponse->json());
    }

    public function test_rango_sin_datos_by_program_retorna_array_vacio(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-by-program?dateFrom=2099-01-01&dateTo=2099-12-31');
        $response->assertOk()->assertJson([]);
    }

    // ─────────────────────────────────────────────
    //  Filtros aplican a todos los endpoints de forma coherente
    // ─────────────────────────────────────────────

    public function test_filtros_son_coherentes_entre_attendances_y_participants(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $att  = $this->getJson("/api/statistics/total-attendances?$q")->json();
        $part = $this->getJson("/api/statistics/total-participants?$q")->json();

        // Verificar números exactos del escenario
        $this->assertEquals(self::WIDE_ATTENDANCES,  $att);
        $this->assertEquals(self::WIDE_PARTICIPANTS, $part);
    }

    public function test_filtros_attendances_over_time_excluye_fechas(): void
    {
        $this->createScenario();

        $response = $this->getJson('/api/statistics/attendances-over-time?' . http_build_query($this->narrowFilter()));
        $response->assertOk();

        $dates = collect($response->json())->pluck('date')->toArray();

        // Solo debe aparecer 2026-03-01 (el evento2)
        $this->assertContains(self::NARROW_DATE, $dates);

        // No deben aparecer fechas fuera del periodo
        $this->assertNotContains('2026-01-10', $dates);
        $this->assertNotContains('2025-11-01', $dates);
    }

    public function test_events_over_time_estructura_correcta(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $this->getJson("/api/statistics/events-over-time?$q")
            ->assertOk()
            ->assertJsonStructure([['date', 'count']]);
    }

    public function test_attendances_over_time_estructura_correcta(): void
    {
        $this->createScenario();
        $q = http_build_query($this->wideFilter());

        $this->getJson("/api/statistics/attendances-over-time?$q")
            ->assertOk()
            ->assertJsonStructure([['date', 'count']]);
    }
}
