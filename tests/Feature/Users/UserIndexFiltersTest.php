<?php

namespace Tests\Feature\Users;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_puede_filtrar_usuarios_por_sede_dependencia_rol_y_estado(): void
    {
        $maicao = Campus::factory()->create(['name' => 'Maicao']);
        $riohacha = Campus::factory()->create(['name' => 'Riohacha']);
        $bienestar = Dependency::factory()->forCampus($maicao)->create(['name' => 'Bienestar']);
        $investigacion = Dependency::factory()->forCampus($riohacha)->create(['name' => 'Investigacion']);

        $superadmin = User::factory()->create([
            'name' => 'Root Principal',
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        $target = User::factory()->create([
            'name' => 'Ana Filtrada',
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
            'is_active' => true,
        ]);
        $target->dependencies()->attach($bienestar->id);

        $otherCampus = User::factory()->create([
            'name' => 'Bruno Riohacha',
            'role' => User::ROLE_USER,
            'campus_id' => $riohacha->id,
            'is_active' => true,
        ]);
        $otherCampus->dependencies()->attach($investigacion->id);

        User::factory()->create([
            'name' => 'Carlos Inactivo',
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
            'is_active' => false,
        ])->dependencies()->attach($bienestar->id);

        User::factory()->create([
            'name' => 'Diana Admin',
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
            'is_active' => true,
        ]);

        $this->actingAs($superadmin)
            ->get(route('users.index', [
                'campus_id' => $maicao->id,
                'dependency_id' => $bienestar->id,
                'role' => User::ROLE_USER,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Ana Filtrada')
            ->assertDontSee('Bruno Riohacha')
            ->assertDontSee('Carlos Inactivo')
            ->assertDontSee('Diana Admin');
    }

    public function test_admin_no_puede_filtrar_usuarios_de_otra_sede(): void
    {
        $maicao = Campus::factory()->create(['name' => 'Maicao']);
        $riohacha = Campus::factory()->create(['name' => 'Riohacha']);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        User::factory()->create([
            'name' => 'Usuario Maicao',
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Usuario Riohacha',
            'role' => User::ROLE_USER,
            'campus_id' => $riohacha->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index', ['campus_id' => $riohacha->id]))
            ->assertOk()
            ->assertSee('Usuario Maicao')
            ->assertDontSee('Usuario Riohacha');
    }
}
