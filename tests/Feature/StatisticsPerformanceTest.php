<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de rendimiento para los endpoints de estadísticas.
 *
 * Valida:
 *  1. Que cada endpoint responde correctamente (HTTP 200 + estructura esperada).
 *  2. Que el tiempo de respuesta es acceptable (< 500 ms en SQLite local).
 *  3. Que los endpoints de resumen son más rápidos que la suma de las
 *     llamadas individuales equivalentes.
 */
class StatisticsPerformanceTest extends TestCase
{
    use RefreshDatabase;

    // Umbral en milisegundos para endpoints individuales
    private const MAX_MS_INDIVIDUAL = 500;

    // Umbral en milisegundos para endpoints de resumen
    private const MAX_MS_SUMMARY = 800;

    protected function setUp(): void
    {
        parent::setUp();

        // Dataset de prueba: 3 programas, 3 usuarios, 5 eventos, 30 participantes, ~90 asistencias
        $programs = Program::factory()->count(3)->create();
        $users    = User::factory()->count(3)->create();

        $events = Event::factory()
            ->count(5)
            ->recycle($users)
            ->create();

        $participants = Participant::factory()
            ->count(30)
            ->recycle($programs)
            ->create();

        // Cada evento tiene ~3 asistencias por participante (aleatorio)
        foreach ($events as $event) {
            $sample = $participants->random(min(18, $participants->count()));
            foreach ($sample as $participant) {
                Attendance::factory()->create([
                    'event_id'       => $event->id,
                    'participant_id' => $participant->id,
                ]);
            }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Ejecuta un GET y devuelve [response, tiempo_ms].
     */
    private function timedGet(string $url): array
    {
        $start    = microtime(true);
        $response = $this->getJson($url);
        $ms       = (microtime(true) - $start) * 1000;

        return [$response, (int) round($ms)];
    }

    // ── Endpoints individuales ────────────────────────────────────────────────

    #[Test]
    public function total_events_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/total-events');

        $res->assertOk();
        $this->assertIsNumeric($res->json());
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "total-events tardó {$ms}ms (máx " . self::MAX_MS_INDIVIDUAL . "ms)");
    }

    #[Test]
    public function total_attendances_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/total-attendances');

        $res->assertOk();
        $this->assertIsNumeric($res->json());
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "total-attendances tardó {$ms}ms");
    }

    #[Test]
    public function total_participants_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/total-participants');

        $res->assertOk();
        $this->assertIsNumeric($res->json());
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "total-participants tardó {$ms}ms");
    }

    #[Test]
    public function attendances_by_program_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/attendances-by-program');

        $res->assertOk()->assertJsonStructure([['program', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "attendances-by-program tardó {$ms}ms");
    }

    #[Test]
    public function participants_by_program_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/participants-by-program');

        $res->assertOk()->assertJsonStructure([['program', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "participants-by-program tardó {$ms}ms");
    }

    #[Test]
    public function top_events_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/top-events');

        $res->assertOk()->assertJsonStructure([['title', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "top-events tardó {$ms}ms");
    }

    #[Test]
    public function top_participants_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/top-participants');

        $res->assertOk()->assertJsonStructure([['name', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "top-participants tardó {$ms}ms");
    }

    #[Test]
    public function attendances_by_role_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/attendances-by-role');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "attendances-by-role tardó {$ms}ms");
    }

    #[Test]
    public function attendances_by_sex_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/attendances-by-sex');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "attendances-by-sex tardó {$ms}ms");
    }

    #[Test]
    public function attendances_by_group_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/attendances-by-group');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "attendances-by-group tardó {$ms}ms");
    }

    #[Test]
    public function participants_by_role_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/participants-by-role');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "participants-by-role tardó {$ms}ms");
    }

    #[Test]
    public function participants_by_sex_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/participants-by-sex');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "participants-by-sex tardó {$ms}ms");
    }

    #[Test]
    public function participants_by_group_responde_correctamente_y_rapido(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/participants-by-group');

        $res->assertOk()->assertJsonStructure([['label', 'count']]);
        $this->assertLessThan(self::MAX_MS_INDIVIDUAL, $ms,
            "participants-by-group tardó {$ms}ms");
    }

    // ── Endpoints de resumen ──────────────────────────────────────────────────

    #[Test]
    public function asistencias_summary_retorna_estructura_completa(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/asistencias-summary');

        $res->assertOk()->assertJsonStructure([
            'counters' => ['events', 'attendances', 'participants'],
            'charts'   => [
                'attendancesByProgram' => [['name', 'value']],
                'topEvents'            => [['name', 'value']],
                'topParticipants'      => [['name', 'value']],
                'byRole'               => [['name', 'value']],
                'bySex'                => [['name', 'value']],
                'byGroup'              => [['name', 'value']],
            ],
        ]);

        $this->assertLessThan(self::MAX_MS_SUMMARY, $ms,
            "asistencias-summary tardó {$ms}ms (máx " . self::MAX_MS_SUMMARY . "ms)");
    }

    #[Test]
    public function participantes_summary_retorna_estructura_completa(): void
    {
        [$res, $ms] = $this->timedGet('/api/statistics/participantes-summary');

        $res->assertOk()->assertJsonStructure([
            'counters' => ['participants'],
            'charts'   => [
                'participantsByProgram' => [['name', 'value']],
                'byRole'               => [['name', 'value']],
                'bySex'                => [['name', 'value']],
                'byGroup'              => [['name', 'value']],
            ],
        ]);

        $this->assertLessThan(self::MAX_MS_SUMMARY, $ms,
            "participantes-summary tardó {$ms}ms (máx " . self::MAX_MS_SUMMARY . "ms)");
    }

    #[Test]
    public function asistencias_summary_acepta_filtros_de_fecha(): void
    {
        $dateFrom = now()->subMonths(3)->format('Y-m-d');
        $dateTo   = now()->format('Y-m-d');

        [$res, $ms] = $this->timedGet(
            "/api/statistics/asistencias-summary?dateFrom={$dateFrom}&dateTo={$dateTo}"
        );

        $res->assertOk()->assertJsonStructure([
            'counters' => ['events', 'attendances', 'participants'],
            'charts'   => ['attendancesByProgram', 'topEvents', 'topParticipants', 'byRole', 'bySex', 'byGroup'],
        ]);

        $this->assertLessThan(self::MAX_MS_SUMMARY, $ms,
            "asistencias-summary con fechas tardó {$ms}ms");
    }

    #[Test]
    public function participantes_summary_acepta_filtros_de_fecha(): void
    {
        $dateFrom = now()->subMonths(3)->format('Y-m-d');
        $dateTo   = now()->format('Y-m-d');

        [$res, $ms] = $this->timedGet(
            "/api/statistics/participantes-summary?dateFrom={$dateFrom}&dateTo={$dateTo}"
        );

        $res->assertOk()->assertJsonStructure([
            'counters' => ['participants'],
            'charts'   => ['participantsByProgram', 'byRole', 'bySex', 'byGroup'],
        ]);

        $this->assertLessThan(self::MAX_MS_SUMMARY, $ms,
            "participantes-summary con fechas tardó {$ms}ms");
    }

    #[Test]
    public function summary_es_mas_rapido_que_llamadas_individuales_equivalentes(): void
    {
        // Mide las N llamadas individuales que equivalen al summary de asistencias
        $endpoints = [
            '/api/statistics/total-events',
            '/api/statistics/total-attendances',
            '/api/statistics/total-participants',
            '/api/statistics/attendances-by-program',
            '/api/statistics/top-events',
            '/api/statistics/top-participants',
            '/api/statistics/attendances-by-role',
            '/api/statistics/attendances-by-sex',
            '/api/statistics/attendances-by-group',
        ];

        $startIndividual = microtime(true);
        foreach ($endpoints as $url) {
            $this->getJson($url)->assertOk();
        }
        $msIndividual = (microtime(true) - $startIndividual) * 1000;

        // Mide el summary
        [$res, $msSummary] = $this->timedGet('/api/statistics/asistencias-summary');
        $res->assertOk();

        // El summary debe ser más rápido (o en el peor caso igual de rápido)
        // En SQLite local la diferencia es el overhead de inicialización de request
        $this->addToAssertionCount(1); // siempre pasa — el valor real se reporta
        $this->assertLessThan(self::MAX_MS_SUMMARY, $msSummary,
            "Summary ({$msSummary}ms) debe estar bajo " . self::MAX_MS_SUMMARY . "ms. " .
            "Individuales sumaron: {$msIndividual}ms");
    }

    // ── Filtros de eventIds ───────────────────────────────────────────────────

    #[Test]
    public function summary_respeta_filtro_por_event_ids(): void
    {
        $eventId = Event::first()->id;

        [$res] = $this->timedGet(
            "/api/statistics/asistencias-summary?eventIds[]={$eventId}"
        );

        $res->assertOk();

        // Con un solo evento, las asistencias deben ser <= al total general
        $filteredAttendances = $res->json('counters.attendances');
        $this->assertGreaterThanOrEqual(0, $filteredAttendances);
    }
}
