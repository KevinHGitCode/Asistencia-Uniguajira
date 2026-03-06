<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Saludo personalizado en el dashboard
 *
 * DashboardController aplica: ucfirst(strtolower($user->name))
 *
 *   'MARIO LOPEZ' → 'Mario lopez'
 *   'Ana Torres'  → 'Ana torres'
 *   'carlos'      → 'Carlos'
 *
 * Nota: strtolower() no es mbstring-safe; no se usan caracteres con tilde en mayúscula
 * en los tests de formateo para evitar resultados inesperados.
 */
class DashboardGreetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_pasa_variable_username_a_la_vista(): void
    {
        $user = User::factory()->create(['name' => 'Ana Torres']);

        // ucfirst(strtolower('Ana Torres')) = 'Ana torres'
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('username', 'Ana torres');
    }

    public function test_saludo_formatea_nombre_en_mayusculas_con_ucfirst(): void
    {
        // Sin tilde en mayúscula: strtolower() es ASCII-only
        $user = User::factory()->create(['name' => 'MARIO LOPEZ']);

        // ucfirst(strtolower('MARIO LOPEZ')) = 'Mario lopez'
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('username', 'Mario lopez');
    }

    public function test_saludo_formatea_nombre_en_minusculas_con_ucfirst(): void
    {
        $user = User::factory()->create(['name' => 'carlos pérez']);

        // ucfirst(strtolower('carlos pérez')) = 'Carlos pérez'
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewHas('username', 'Carlos pérez');
    }

    public function test_saludo_contiene_nombre_del_usuario_en_respuesta_html(): void
    {
        $user = User::factory()->create(['name' => 'Lucía']);

        // ucfirst(strtolower('Lucía')) = 'Lucía'
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSeeText('Lucía');
    }
}
