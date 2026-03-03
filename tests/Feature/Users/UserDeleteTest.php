<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Control de acceso
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('users.delete', $user->id), ['password' => 'cualquiera'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_usuario_regular_recibe_403(): void
    {
        $actor  = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create();

        $this->actingAs($actor)
            ->post(route('users.delete', $target->id), ['password' => 'password'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    // ─────────────────────────────────────────────
    //  Eliminación exitosa
    // ─────────────────────────────────────────────

    public function test_admin_puede_eliminar_usuario_con_contrasena_correcta(): void
    {
        $admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('contrasena-correcta'),
        ]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.delete', $user->id), [
                'password' => 'contrasena-correcta',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_eliminacion_redirige_con_mensaje_de_exito(): void
    {
        $admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('contrasena-correcta'),
        ]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.delete', $user->id), [
                'password' => 'contrasena-correcta',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
    }

    // ─────────────────────────────────────────────
    //  Contrasena incorrecta
    // ─────────────────────────────────────────────

    public function test_falla_con_contrasena_incorrecta(): void
    {
        $admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('contrasena-correcta'),
        ]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.delete', $user->id), [
                'password' => 'contrasena-incorrecta',
            ])
            ->assertSessionHasErrors('password');

        // El usuario no debe haberse eliminado
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_falla_sin_contrasena(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        // Sin password: Hash::check(null, hash) retorna false → error
        $this->actingAs($admin)
            ->post(route('users.delete', $user->id), [])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    // ─────────────────────────────────────────────
    //  Integridad referencial
    // ─────────────────────────────────────────────

    public function test_retorna_404_para_usuario_inexistente(): void
    {
        $admin = User::factory()->create([
            'role'     => 'admin',
            'password' => Hash::make('contrasena-correcta'),
        ]);

        $this->actingAs($admin)
            ->post(route('users.delete', 99999), [
                'password' => 'contrasena-correcta',
            ])
            ->assertNotFound();
    }

    public function test_vista_information_muestra_accion_visual_de_eliminar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.information', $user->id))
            ->assertOk()
            ->assertSee('Eliminar')
            ->assertSee('action="' . route('users.delete', $user->id) . '"', false)
            ->assertSee('name="password"', false);
    }
}
