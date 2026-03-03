<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Control de acceso
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('user.update', $user->id), [
            'name'  => 'Nuevo',
            'email' => 'nuevo@example.com',
        ])->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403(): void
    {
        $actor  = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create();

        $this->actingAs($actor)
            ->post(route('user.update', $target->id), [
                'name'  => 'Hack',
                'email' => 'hack@example.com',
            ])
            ->assertForbidden();
    }

    public function test_admin_puede_ver_formulario_de_edicion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create([
            'name' => 'Usuario Editar',
            'email' => 'editar@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('user.edit', $user->id))
            ->assertOk()
            ->assertViewIs('users.edit')
            ->assertSee('Editar usuario')
            ->assertSee('value="Usuario Editar"', false)
            ->assertSee('value="editar@example.com"', false)
            ->assertSee('action="' . route('user.update', $user->id) . '"', false);
    }

    // ─────────────────────────────────────────────
    //  Actualización exitosa
    // ─────────────────────────────────────────────

    public function test_admin_puede_actualizar_nombre_y_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create([
            'name'  => 'Nombre Viejo',
            'email' => 'viejo@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name'  => 'Nombre Nuevo',
                'email' => 'nuevo@example.com',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Nombre Nuevo',
            'email' => 'nuevo@example.com',
        ]);
    }

    public function test_update_redirige_con_mensaje_de_exito(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name'  => 'Actualizado',
                'email' => 'actualizado@example.com',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
    }

    public function test_usuario_puede_mantener_su_propio_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['email' => 'mismo@example.com']);

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name'  => 'Nombre Actualizado',
                'email' => 'mismo@example.com', // mismo email — debe pasar
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Nombre Actualizado',
        ]);
    }

    // ─────────────────────────────────────────────
    //  Validaciones
    // ─────────────────────────────────────────────

    public function test_falla_sin_nombre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'email' => 'test@example.com',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_falla_sin_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name' => 'Nombre',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_falla_con_email_invalido(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name'  => 'Nombre',
                'email' => 'no-es-email',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_falla_con_email_de_otro_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();
        User::factory()->create(['email' => 'ocupado@example.com']);

        $this->actingAs($admin)
            ->post(route('user.update', $user->id), [
                'name'  => 'Nombre',
                'email' => 'ocupado@example.com',
            ])
            ->assertSessionHasErrors('email');
    }

    // ─────────────────────────────────────────────
    //  Usuario no encontrado
    // ─────────────────────────────────────────────

    public function test_retorna_404_para_usuario_inexistente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('user.update', 99999), [
                'name'  => 'Test',
                'email' => 'test@example.com',
            ])
            ->assertNotFound();
    }
}
