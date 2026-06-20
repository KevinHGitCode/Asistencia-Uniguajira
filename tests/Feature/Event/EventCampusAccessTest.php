<?php

namespace Tests\Feature\Event;

use App\Livewire\Event\EditEventModal;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use App\Services\CampusScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventCampusAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_no_puede_ver_evento_de_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);
        $event = Event::factory()->create([
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($admin)
            ->get(route('events.show', $event))
            ->assertForbidden();
    }

    public function test_admin_solo_ve_eventos_de_su_sede_en_listado(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        Event::factory()->create([
            'title' => 'Evento Maicao',
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento Riohacha',
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($admin)
            ->get(route('events.list'))
            ->assertOk()
            ->assertSee('Evento Maicao')
            ->assertDontSee('Evento Riohacha');
    }

    public function test_admin_no_puede_eliminar_evento_de_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);
        $event = Event::factory()->create([
            'campus_id' => $riohacha->id,
            'date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->delete(route('events.destroy', $event))
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_user_no_puede_crear_evento_con_dependencia_de_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
        ]);
        $dependency = Dependency::factory()->create([
            'campus_id' => $riohacha->id,
        ]);
        $user->dependencies()->attach($dependency);

        $this->actingAs($user)
            ->post(route('events.new.store'), [
                'title' => 'Evento cruzado',
                'description' => null,
                'date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'location' => 'Auditorio',
                'dependency_id' => $dependency->id,
                'area_id' => null,
            ])
            ->assertSessionHasErrors('dependency_id');

        $this->assertDatabaseMissing('events', ['title' => 'Evento cruzado']);
    }

    public function test_admin_no_puede_editar_evento_de_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);
        $event = Event::factory()->create([
            'title' => 'Evento Riohacha',
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditEventModal::class)
            ->call('loadEvent', $event->id)
            ->assertSet('eventId', null);
    }

    public function test_superadmin_sin_sede_puede_acceder_a_evento_de_cualquier_sede(): void
    {
        [, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);
        $event = Event::factory()->create([
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($superadmin)
            ->get(route('events.show', $event))
            ->assertOk();
    }

    public function test_superadmin_con_sede_activa_solo_lista_eventos_de_esa_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create([
            'title' => 'Evento Maicao',
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento Riohacha',
            'campus_id' => $riohacha->id,
        ]);

        $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->get(route('events.list'))
            ->assertOk()
            ->assertDontSee('Evento Maicao')
            ->assertSee('Evento Riohacha');
    }

    public function test_superadmin_con_sede_activa_no_accede_a_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);
        $event = Event::factory()->create([
            'campus_id' => $riohacha->id,
        ]);

        $this->withSession([CampusScopeService::SESSION_KEY => $maicao->id])
            ->actingAs($superadmin)
            ->get(route('events.show', $event))
            ->assertForbidden();
    }

    public function test_ruta_publica_por_slug_sigue_funcionando(): void
    {
        [, $riohacha] = $this->campuses();
        $event = Event::factory()->create([
            'campus_id' => $riohacha->id,
            'link' => 'evento-publico-campus',
        ]);

        $this->get(route('events.access', $event->link))->assertOk();
    }

    private function campuses(): array
    {
        return [
            Campus::create(['name' => 'Maicao']),
            Campus::create(['name' => 'Riohacha']),
        ];
    }
}
