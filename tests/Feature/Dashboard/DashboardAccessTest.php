<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Control de acceso al dashboard
 *
 * Verifica que:
 *   - Invitados son redirigidos al login
 *   - Usuarios autenticados (cualquier rol) pueden acceder
 *   - La vista correcta es retornada
 */
class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_puede_acceder_al_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_admin_puede_acceder_al_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_dashboard_retorna_la_vista_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertViewIs('dashboard');
    }
}
