<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paleta de comandos para administradores (ADR-0007).
 *
 * Verifica que la isla Alpine + su disparador solo se montan para usuarios con
 * acceso de administrador, y que el registro de comandos incluye los módulos
 * esperados (y los exclusivos de superadmin solo para superadmin).
 */
class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_la_paleta_de_comandos(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('commandPalette(', false);
        $response->assertSee('open-command-palette', false);
        // Comandos representativos.
        $response->assertSee(route('users.index'), false);
        $response->assertDontSee(route('formats.index'), false);
        $response->assertDontSee(route('activity-logs.index'), false);
    }

    public function test_usuario_regular_no_ve_la_paleta_de_comandos(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('commandPalette(', false);
        $response->assertDontSee('open-command-palette', false);
    }

    public function test_comando_de_sedes_solo_para_superadmin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);

        // Admin normal: sin comandos exclusivos de superadmin.
        $this->actingAs($admin)->get('/dashboard')
            ->assertDontSee(route('campuses.index'), false)
            ->assertDontSee(route('formats.index'), false)
            ->assertDontSee(route('activity-logs.index'), false);

        // Superadmin: con comandos exclusivos.
        $this->actingAs($superadmin)->get('/dashboard')
            ->assertSee(route('campuses.index'), false)
            ->assertSee(route('formats.index'), false)
            ->assertSee(route('activity-logs.index'), false);
    }
}
