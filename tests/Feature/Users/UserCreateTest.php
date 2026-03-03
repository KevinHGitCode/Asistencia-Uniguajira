<?php

namespace Tests\Feature\Users;

use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Formulario de creación (GET /usuarios/create)
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('user.form'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403_al_ver_formulario(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('user.form'))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_formulario_de_creacion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('user.form'))
            ->assertOk()
            ->assertViewIs('users.create');
    }

    public function test_formulario_incluye_lista_de_dependencias(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Dependency::factory(3)->create();

        $this->actingAs($admin)
            ->get(route('user.form'))
            ->assertViewHas('dependencies', function ($deps) {
                return count($deps) === 3;
            });
    }

    public function test_formulario_incluye_roles_disponibles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('user.form'))
            ->assertViewHas('roles', function ($roles) {
                return array_key_exists('admin', $roles)
                    && array_key_exists('user', $roles);
            });
    }

    public function test_formulario_muestra_campos_y_boton_crear(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('user.form'))
            ->assertOk()
            ->assertSee('Nuevo usuario')
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="role"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Crear usuario');
    }

    // ─────────────────────────────────────────────
    //  Guardar usuario (POST /usuarios)
    // ─────────────────────────────────────────────

    public function test_admin_puede_crear_usuario_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Nuevo Admin',
                'email'    => 'nuevo.admin@uniguajira.edu.co',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo.admin@uniguajira.edu.co',
            'role'  => 'admin',
        ]);
    }

    public function test_admin_puede_crear_usuario_regular_con_dependencia(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $dependency = Dependency::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'          => 'Usuario Regular',
                'email'         => 'usuario@uniguajira.edu.co',
                'password'      => 'secreta123',
                'role'          => 'user',
                'dependency_id' => $dependency->id,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'usuario@uniguajira.edu.co',
            'role'  => 'user',
        ]);
    }

    public function test_store_redirige_con_mensaje_de_exito(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'test@example.com',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');
    }

    // ─────────────────────────────────────────────
    //  Validaciones
    // ─────────────────────────────────────────────

    public function test_falla_sin_nombre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'email'    => 'test@example.com',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_falla_sin_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_falla_con_email_invalido(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'no-es-un-email',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_falla_con_email_duplicado(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['email' => 'existente@example.com']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Copia',
                'email'    => 'existente@example.com',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_falla_sin_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'  => 'Test',
                'email' => 'test@example.com',
                'role'  => 'admin',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_falla_con_password_corta(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'test@example.com',
                'password' => '123',
                'role'     => 'admin',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_falla_con_rol_invalido(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'test@example.com',
                'password' => 'secreta123',
                'role'     => 'superadmin',
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_falla_cuando_rol_user_no_tiene_dependency_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Test',
                'email'    => 'test@example.com',
                'password' => 'secreta123',
                'role'     => 'user',
                // sin dependency_id
            ])
            ->assertSessionHasErrors('dependency_id');
    }

    public function test_falla_con_dependency_id_inexistente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'          => 'Test',
                'email'         => 'test@example.com',
                'password'      => 'secreta123',
                'role'          => 'user',
                'dependency_id' => 99999,
            ])
            ->assertSessionHasErrors('dependency_id');
    }

    public function test_admin_no_requiere_dependency_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name'     => 'Otro Admin',
                'email'    => 'otro.admin@example.com',
                'password' => 'secreta123',
                'role'     => 'admin',
                // sin dependency_id — válido para admin
            ])
            ->assertRedirect(route('users.index'));
    }

    public function test_usuario_regular_no_puede_crear_usuarios(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('users.store'), [
                'name'     => 'Hack',
                'email'    => 'hack@example.com',
                'password' => 'secreta123',
                'role'     => 'admin',
            ])
            ->assertForbidden();
    }
}
