<?php

namespace Tests\Feature\Livewire;

use App\Livewire\User\CreateUserModal;
use App\Livewire\User\EditUserModal;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserDependencyFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_modales_de_usuario_filtran_dependencias_por_sede(): void
    {
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $maicao = Campus::create(['name' => 'Maicao']);
        $villanueva = Campus::create(['name' => 'Villanueva']);
        $maicaoDependency = Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $maicao->id]);
        Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $villanueva->id]);
        $props = [
            'dependencies' => [],
            'campuses' => [],
            'roles' => [User::ROLE_USER => 'Usuario'],
        ];

        $this->actingAs($superadmin);

        foreach ([CreateUserModal::class, EditUserModal::class] as $component) {
            Livewire::test($component, $props)
                ->set('role', User::ROLE_USER)
                ->set('campus_id', (string) $maicao->id)
                ->assertSet('filteredDependencies', [[
                    'id' => $maicaoDependency->id,
                    'name' => 'Aseguramiento de la calidad',
                ]]);
        }
    }
}
