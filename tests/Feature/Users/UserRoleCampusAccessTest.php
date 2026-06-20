<?php

namespace Tests\Feature\Users;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleCampusAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_acceder_a_usuarios(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $campus->id,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_superadmin_puede_acceder_a_usuarios(): void
    {
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        $this->actingAs($superadmin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_admin_no_puede_crear_superadmin(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $campus->id,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => 'secreta123',
                'role' => User::ROLE_SUPERADMIN,
                'campus_id' => null,
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_admin_no_puede_asignar_otra_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $dependency = Dependency::factory()->create([
            'campus_id' => $riohacha->id,
        ]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Usuario Riohacha',
                'email' => 'riohacha@example.com',
                'password' => 'secreta123',
                'role' => User::ROLE_USER,
                'campus_id' => $riohacha->id,
                'dependency_id' => $dependency->id,
            ])
            ->assertSessionHasErrors('campus_id');
    }
}
