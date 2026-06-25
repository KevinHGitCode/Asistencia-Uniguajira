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

    public function test_admin_solo_ve_sus_eventos_en_listado(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);
        $otherAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        Event::factory()->create([
            'title' => 'Evento propio Maicao',
            'user_id' => $admin->id,
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento ajeno Maicao',
            'user_id' => $otherAdmin->id,
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento propio Riohacha',
            'user_id' => $admin->id,
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($admin)
            ->get(route('events.list'))
            ->assertOk()
            ->assertSee('Evento propio Maicao')
            ->assertDontSee('Evento ajeno Maicao')
            ->assertDontSee('Evento propio Riohacha');
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

    public function test_superadmin_lista_solo_sus_eventos_sin_heredar_sede_activa(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);
        $otherUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        Event::factory()->create([
            'title' => 'Evento propio Maicao',
            'user_id' => $superadmin->id,
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento propio Riohacha',
            'user_id' => $superadmin->id,
            'campus_id' => $riohacha->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento de otro usuario',
            'user_id' => $otherUser->id,
            'campus_id' => $maicao->id,
        ]);

        $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->get(route('events.list'))
            ->assertOk()
            ->assertSee('Filtrar tus eventos por sede')
            ->assertSee('Todas mis sedes')
            ->assertSee('Evento propio Maicao')
            ->assertSee('Evento propio Riohacha')
            ->assertDontSee('Evento de otro usuario');
    }

    public function test_superadmin_filtra_sus_eventos_por_sede_en_listado(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create([
            'title' => 'Evento propio Maicao',
            'user_id' => $superadmin->id,
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'title' => 'Evento propio Riohacha',
            'user_id' => $superadmin->id,
            'campus_id' => $riohacha->id,
        ]);

        $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->get(route('events.list', ['campus_id' => $maicao->id]))
            ->assertOk()
            ->assertSee('Evento propio Maicao')
            ->assertDontSee('Evento propio Riohacha')
            ->assertSee('value="'.$maicao->id.'" selected', false);
    }

    public function test_superadmin_admin_eventos_no_hereda_sede_activa(): void
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

        $response = $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/statistics/admin-eventos');

        $titles = collect($response->json('events'))->pluck('title')->all();

        $response->assertOk()->assertJsonPath('selected_campus_id', null);
        $this->assertContains('Evento Maicao', $titles);
        $this->assertContains('Evento Riohacha', $titles);
    }

    public function test_superadmin_admin_eventos_filtra_por_sede_del_modulo(): void
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

        $response = $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/statistics/admin-eventos?campus_id='.$maicao->id);

        $titles = collect($response->json('events'))->pluck('title')->all();

        $response->assertOk()->assertJsonPath('selected_campus_id', $maicao->id);
        $this->assertContains('Evento Maicao', $titles);
        $this->assertNotContains('Evento Riohacha', $titles);
    }

    public function test_superadmin_admin_eventos_opciones_filtran_por_sede_del_modulo(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);
        $maicaoDependency = Dependency::factory()->create([
            'name' => 'Dependencia Maicao',
            'campus_id' => $maicao->id,
        ]);
        $riohachaDependency = Dependency::factory()->create([
            'name' => 'Dependencia Riohacha',
            'campus_id' => $riohacha->id,
        ]);

        Event::factory()->create([
            'dependency_id' => $maicaoDependency->id,
            'campus_id' => $maicao->id,
        ]);
        Event::factory()->create([
            'dependency_id' => $riohachaDependency->id,
            'campus_id' => $riohacha->id,
        ]);

        $response = $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/statistics/admin-eventos/filter-options?campus_id='.$maicao->id);

        $dependencies = collect($response->json('dependencies'))->pluck('name')->all();

        $response->assertOk()
            ->assertJsonPath('show_campuses', true)
            ->assertJsonPath('selected_campus_id', $maicao->id);
        $this->assertContains('Dependencia Maicao', $dependencies);
        $this->assertNotContains('Dependencia Riohacha', $dependencies);
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
