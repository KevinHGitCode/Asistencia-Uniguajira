<?php

namespace Tests\Feature\Dashboard;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Calendario del dashboard — endpoints de la API
 *
 * Endpoints verificados:
 *   GET /api/eventos-json       — datos de heatmap del semestre actual (público)
 *   GET /api/mis-eventos-json   — datos del usuario autenticado (requiere auth)
 *   GET /api/events/{date}      — eventos de un día específico (público)
 *
 * Lógica de semestre en /api/eventos-json y /api/mis-eventos-json:
 *   - Meses 1–6  → 1 de enero al 30 de junio del año actual
 *   - Meses 7–12 → 1 de julio al 31 de diciembre del año actual
 *
 * @note /api/mis-eventos-json usa DATE_FORMAT() (MySQL). El test de contenido
 *       se omite automáticamente en entornos SQLite.
 */
class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Fija el tiempo al primer semestre de 2026 (enero–junio)
        // para que la lógica de semestre sea predecible en todos los tests.
        Carbon::setTestNow('2026-03-01');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Resetea el tiempo global
        parent::tearDown();
    }

    // ─────────────────────────────────────────────
    //  GET /api/eventos-json  (público)
    // ─────────────────────────────────────────────

    public function test_eventos_json_retorna_ok_sin_autenticacion(): void
    {
        $this->getJson('/api/eventos-json')->assertOk();
    }

    public function test_eventos_json_retorna_un_array(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-10']);

        $this->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJsonIsArray();
    }

    public function test_eventos_json_incluye_eventos_dentro_del_semestre(): void
    {
        // Semestre actual (con now() = 2026-03-01): 2026-01-01 a 2026-06-30
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-10']);

        $response = $this->getJson('/api/eventos-json');
        $response->assertOk();

        $dates = collect($response->json())->pluck('date')->toArray();
        $this->assertContains('2026-02-10', $dates);
    }

    public function test_eventos_json_excluye_eventos_fuera_del_semestre(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Fuera del primer semestre 2026
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-07-15']); // segundo semestre
        Event::factory()->create(['user_id' => $user->id, 'date' => '2025-12-01']); // año anterior

        $response = $this->getJson('/api/eventos-json');
        $dates = collect($response->json())->pluck('date')->toArray();

        $this->assertNotContains('2026-07-15', $dates);
        $this->assertNotContains('2025-12-01', $dates);
    }

    public function test_eventos_json_agrupa_multiples_eventos_del_mismo_dia(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory(3)->create(['user_id' => $user->id, 'date' => '2026-03-15']);

        $response = $this->getJson('/api/eventos-json');
        $response->assertOk();

        $entry = collect($response->json())->firstWhere('date', '2026-03-15');
        $this->assertNotNull($entry, 'La fecha 2026-03-15 debe aparecer en el JSON');
        $this->assertEquals(3, $entry['count']);
    }

    public function test_eventos_json_retorna_estructura_correcta(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-04-10']);

        $this->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJsonStructure([['date', 'count']]);
    }

    public function test_eventos_json_retorna_array_vacio_sin_eventos_en_el_semestre(): void
    {
        // No hay eventos en el semestre actual
        $this->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJson([]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/mis-eventos-json  (requiere auth)
    // ─────────────────────────────────────────────

    /**
     * La ruta usa middleware(['web','auth']): el guard web redirige al login.
     */
    public function test_mis_eventos_json_redirige_a_login_sin_autenticacion(): void
    {
        $this->get('/api/mis-eventos-json')
            ->assertRedirect(route('login'));
    }

    /**
     * @note La ruta usa DATE_FORMAT() (MySQL-only). En SQLite este test
     *       se salta automáticamente ya que SQLite no soporta esa función.
     */
    public function test_mis_eventos_json_retorna_auth_id_del_usuario_autenticado(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped(
                'DATE_FORMAT() en /api/mis-eventos-json no es compatible con SQLite. Requiere MySQL.'
            );
        }

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->getJson('/api/mis-eventos-json')
            ->assertOk()
            ->assertJsonPath('auth_id', $user->id);
    }

    // ─────────────────────────────────────────────
    //  GET /api/events/{date}  (público)
    // ─────────────────────────────────────────────

    public function test_events_by_date_retorna_ok(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-03-15']);

        $this->getJson('/api/events/2026-03-15')->assertOk();
    }

    public function test_events_by_date_retorna_solo_los_eventos_del_dia(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Event::factory(2)->create(['user_id' => $user->id, 'date' => '2026-03-15']);
        Event::factory(1)->create(['user_id' => $user->id, 'date' => '2026-03-16']); // otro día

        $response = $this->getJson('/api/events/2026-03-15');
        $response->assertOk();

        $this->assertCount(2, $response->json());
    }

    public function test_events_by_date_retorna_array_vacio_para_fecha_sin_eventos(): void
    {
        $this->getJson('/api/events/2030-06-15')
            ->assertOk()
            ->assertJson([]);
    }

    public function test_events_by_date_es_publico_sin_necesidad_de_autenticacion(): void
    {
        // No requiere auth — ruta pública
        $this->getJson('/api/events/2026-03-15')->assertOk();
    }

    public function test_events_by_date_incluye_eventos_de_todos_los_usuarios(): void
    {
        // La ruta no filtra por user_id: devuelve todos los eventos del día
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-03-15']);
        Event::factory()->create(['user_id' => $user->id,  'date' => '2026-03-15']);

        $response = $this->getJson('/api/events/2026-03-15');
        $response->assertOk();

        $this->assertCount(2, $response->json());
    }
}
