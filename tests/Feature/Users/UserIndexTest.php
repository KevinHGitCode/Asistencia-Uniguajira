<?php

namespace Tests\Feature\Users;

use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Control de acceso
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_la_lista_de_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertViewIs('users.index');
    }

    // ─────────────────────────────────────────────
    //  Datos en la vista
    // ─────────────────────────────────────────────

    public function test_vista_recibe_variable_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertViewHas('users');
    }

    public function test_lista_incluye_a_todos_los_usuarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory(3)->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertViewHas('users', function ($users) {
            return $users->count() === 4; // 3 nuevos + el admin
        });
    }

    public function test_usuarios_se_cargan_con_dependencias_y_conteo_de_eventos(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $dependency = Dependency::factory()->create();
        $user       = User::factory()->create(['role' => 'user']);
        $user->dependencies()->attach($dependency);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertViewHas('users', function ($users) use ($user) {
            $found = $users->firstWhere('id', $user->id);
            return $found !== null
                && isset($found->dependencies_count)
                && isset($found->events_count);
        });
    }

    public function test_lista_funciona_sin_usuarios_adicionales(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) {
            return $users->count() === 1;
        });
    }

    public function test_lista_muestra_enlaces_de_informacion_y_edicion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee(route('users.information', $user->id), false)
            ->assertSee(route('user.edit', $user->id), false);
    }
}
