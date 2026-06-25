<?php

namespace Tests\Feature\Livewire;

use App\Livewire\User\EditUserModal;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El estado activo/inactivo se cambia con un selector aplicado al guardar el
 * modal de edición, respetando la jerarquía (ADR-0010, frente 1).
 */
class UserEditModalToggleTest extends TestCase
{
    use RefreshDatabase;

    private function editModal(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(EditUserModal::class, [
            'dependencies' => collect(),
            'campuses' => [],
            'roles' => [],
        ]);
    }

    public function test_superadmin_puede_desactivar_a_un_administrador_al_guardar(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $campus->id, 'is_active' => true]);

        $this->actingAs($superadmin);

        $this->editModal()
            ->call('loadUser', $admin->id)
            ->assertSet('canToggleActive', true)
            ->set('activeState', '0')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertFalse((bool) $admin->fresh()->is_active);
    }

    public function test_admin_no_puede_cambiar_el_estado_de_otro_administrador(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $campus->id]);
        $otherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $campus->id, 'is_active' => true]);

        $this->actingAs($admin);

        // El selector no se ofrece, y aunque se fuerce activeState el servidor lo ignora.
        $this->editModal()
            ->call('loadUser', $otherAdmin->id)
            ->assertSet('canToggleActive', false)
            ->set('activeState', '0')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertTrue((bool) $otherAdmin->fresh()->is_active);
    }

    public function test_admin_puede_desactivar_a_un_usuario_de_su_sede_al_guardar(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $dependency = Dependency::factory()->create(['campus_id' => $campus->id]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $campus->id]);
        $user = User::factory()->create(['role' => User::ROLE_USER, 'campus_id' => $campus->id, 'is_active' => true]);
        $user->dependencies()->attach($dependency->id);

        $this->actingAs($admin);

        $this->editModal()
            ->call('loadUser', $user->id)
            ->assertSet('canToggleActive', true)
            ->set('activeState', '0')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_nadie_puede_cambiar_su_propio_estado(): void
    {
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);

        $this->actingAs($superadmin);

        $this->editModal()
            ->call('loadUser', $superadmin->id)
            ->assertSet('canToggleActive', false);
    }
}
