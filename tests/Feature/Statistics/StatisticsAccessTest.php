<?php

namespace Tests\Feature\Statistics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que las páginas del módulo de estadísticas respondan
 * correctamente según el rol del usuario.
 *
 * Las páginas (Blade + React island) deben:
 *   - Redirigir al login si no hay sesión
 *   - Ser accesibles para cualquier usuario autenticado (asistencias, participantes, eventos)
 *   - Requerir rol admin para "Por Usuarios"
 */
class StatisticsAccessTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Página principal de estadísticas
    // ─────────────────────────────────────────────

    public function test_invitado_no_puede_ver_pagina_principal(): void
    {
        $this->get(route('statistics'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_autenticado_puede_ver_pagina_principal(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics'))
            ->assertOk();
    }

    public function test_admin_puede_ver_pagina_principal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('statistics'))
            ->assertOk();
    }

    // ─────────────────────────────────────────────
    //  Módulo Por Asistencias
    // ─────────────────────────────────────────────

    public function test_invitado_no_puede_ver_asistencias(): void
    {
        $this->get(route('statistics.asistencias'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_puede_ver_asistencias(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.asistencias'))
            ->assertOk();
    }

    public function test_pagina_asistencias_monta_react_island_correcto(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.asistencias'))
            ->assertSee('id="statistics-react-root"', false)
            ->assertSee('data-module="asistencias"', false);
    }

    // ─────────────────────────────────────────────
    //  Módulo Por Participantes
    // ─────────────────────────────────────────────

    public function test_invitado_no_puede_ver_participantes(): void
    {
        $this->get(route('statistics.participantes'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_puede_ver_participantes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.participantes'))
            ->assertOk();
    }

    public function test_pagina_participantes_monta_react_island_correcto(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.participantes'))
            ->assertSee('id="statistics-react-root"', false)
            ->assertSee('data-module="participantes"', false);
    }

    // ─────────────────────────────────────────────
    //  Módulo Por Usuarios — solo admin
    // ─────────────────────────────────────────────

    public function test_invitado_no_puede_ver_usuarios(): void
    {
        $this->get(route('statistics.usuarios'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403_en_usuarios(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('statistics.usuarios'))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_modulo_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('statistics.usuarios'))
            ->assertOk();
    }

    public function test_pagina_usuarios_monta_react_island_correcto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('statistics.usuarios'))
            ->assertSee('id="statistics-react-root"', false)
            ->assertSee('data-module="usuarios"', false);
    }

    // ─────────────────────────────────────────────
    //  API de estadísticas — acceso público (bug conocido)
    // ─────────────────────────────────────────────

    /**
     * NOTA: Los endpoints /api/statistics/* son actualmente públicos.
     * Esto es un bug — deberían requerir autenticación.
     * Estos tests documentan el comportamiento actual.
     *
     * @see https://github.com/tu-repo/issues/XX (pendiente de corrección)
     */
    public function test_api_total_events_es_accesible_sin_autenticacion(): void
    {
        $this->getJson('/api/statistics/total-events')
            ->assertOk();
    }

    public function test_api_total_attendances_es_accesible_sin_autenticacion(): void
    {
        $this->getJson('/api/statistics/total-attendances')
            ->assertOk();
    }

    public function test_api_total_participants_es_accesible_sin_autenticacion(): void
    {
        $this->getJson('/api/statistics/total-participants')
            ->assertOk();
    }
}
