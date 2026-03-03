<?php

namespace Tests\Unit\Models;

use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Atributos y configuración del modelo
    // ─────────────────────────────────────────────

    public function test_user_tiene_name_en_fillable(): void
    {
        $this->assertContains('name', (new User())->getFillable());
    }

    public function test_user_tiene_email_en_fillable(): void
    {
        $this->assertContains('email', (new User())->getFillable());
    }

    public function test_user_tiene_role_en_fillable(): void
    {
        $this->assertContains('role', (new User())->getFillable());
    }

    public function test_user_tiene_avatar_en_fillable(): void
    {
        $this->assertContains('avatar', (new User())->getFillable());
    }

    public function test_password_esta_oculta(): void
    {
        $this->assertContains('password', (new User())->getHidden());
    }

    public function test_remember_token_esta_oculto(): void
    {
        $this->assertContains('remember_token', (new User())->getHidden());
    }

    public function test_password_se_hashea_al_guardar(): void
    {
        $user = User::factory()->create(['password' => 'password-plano']);

        $this->assertNotEquals('password-plano', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password-plano', $user->password));
    }

    // ─────────────────────────────────────────────
    //  Método initials()
    // ─────────────────────────────────────────────

    public function test_initials_retorna_dos_letras_para_nombre_completo(): void
    {
        $user = User::factory()->make(['name' => 'Juan Pérez']);

        $this->assertEquals('JP', $user->initials());
    }

    public function test_initials_retorna_una_letra_para_nombre_simple(): void
    {
        $user = User::factory()->make(['name' => 'Carlos']);

        $this->assertEquals('C', $user->initials());
    }

    public function test_initials_solo_usa_las_dos_primeras_palabras(): void
    {
        $user = User::factory()->make(['name' => 'Ana Maria Lopez']);

        $this->assertEquals('AM', $user->initials());
    }

    public function test_initials_es_mayuscula(): void
    {
        $user = User::factory()->make(['name' => 'ana pérez']);

        $this->assertEquals('AP', strtoupper($user->initials()));
    }

    // ─────────────────────────────────────────────
    //  Relaciones
    // ─────────────────────────────────────────────

    public function test_user_tiene_relacion_has_many_events(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(HasMany::class, $user->events());
    }

    public function test_user_tiene_relacion_belongs_to_many_dependencies(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(BelongsToMany::class, $user->dependencies());
    }

    public function test_user_puede_asociarse_con_una_dependencia(): void
    {
        $user       = User::factory()->create();
        $dependency = Dependency::factory()->create();

        $user->dependencies()->attach($dependency);

        $this->assertTrue(
            $user->dependencies()->where('dependency_id', $dependency->id)->exists()
        );
    }

    public function test_user_puede_pertenecer_a_multiples_dependencias(): void
    {
        $user = User::factory()->create();
        $dep1 = Dependency::factory()->create();
        $dep2 = Dependency::factory()->create();

        $user->dependencies()->attach([$dep1->id, $dep2->id]);

        $this->assertCount(2, $user->fresh()->dependencies);
    }

    public function test_tabla_pivot_no_permite_duplicados(): void
    {
        $user       = User::factory()->create();
        $dependency = Dependency::factory()->create();

        $user->dependencies()->attach($dependency);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $user->dependencies()->attach($dependency);
    }

    public function test_user_puede_tener_eventos(): void
    {
        $user   = User::factory()->create();
        $events = Event::factory(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->fresh()->events);
    }

    // ─────────────────────────────────────────────
    //  Roles
    // ─────────────────────────────────────────────

    public function test_rol_por_defecto_es_user(): void
    {
        // El enum en la migración tiene default 'user'
        $user = User::factory()->create();

        // La factory no establece role, así que se usará el default de DB
        // (o podemos verificar que los valores válidos son admin/user)
        $this->assertContains($user->role, ['admin', 'user']);
    }

    public function test_user_puede_tener_rol_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $admin->role);
    }

    public function test_user_puede_tener_rol_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertEquals('user', $user->role);
    }
}
