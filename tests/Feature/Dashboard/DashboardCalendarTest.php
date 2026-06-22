<?php

namespace Tests\Feature\Dashboard;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use App\Services\CampusScopeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-01');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_eventos_json_requiere_autenticacion(): void
    {
        $this->getJson('/api/eventos-json')->assertUnauthorized();
    }

    public function test_eventos_json_retorna_un_array_para_usuario_autenticado(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-10']);

        $this->actingAs($user)
            ->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJsonIsArray();
    }

    public function test_eventos_json_incluye_eventos_dentro_del_semestre(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-02-10']);

        $response = $this->actingAs($user)->getJson('/api/eventos-json');

        $dates = collect($response->json())->pluck('date')->toArray();
        $this->assertContains('2026-02-10', $dates);
    }

    public function test_eventos_json_excluye_eventos_fuera_del_semestre(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-07-15']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2025-12-01']);

        $response = $this->actingAs($user)->getJson('/api/eventos-json');
        $dates = collect($response->json())->pluck('date')->toArray();

        $this->assertNotContains('2026-07-15', $dates);
        $this->assertNotContains('2025-12-01', $dates);
    }

    public function test_eventos_json_agrupa_multiples_eventos_del_mismo_dia(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory(3)->create(['user_id' => $user->id, 'date' => '2026-03-15']);

        $response = $this->actingAs($user)->getJson('/api/eventos-json');

        $entry = collect($response->json())->firstWhere('date', '2026-03-15');
        $this->assertNotNull($entry, 'La fecha 2026-03-15 debe aparecer en el JSON');
        $this->assertEquals(3, $entry['count']);
    }

    public function test_eventos_json_retorna_estructura_correcta(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-04-10']);

        $this->actingAs($user)
            ->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJsonStructure([['date', 'count']]);
    }

    public function test_eventos_json_retorna_array_vacio_sin_eventos_en_el_semestre(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)
            ->getJson('/api/eventos-json')
            ->assertOk()
            ->assertJson([]);
    }

    public function test_mis_eventos_json_redirige_a_login_sin_autenticacion(): void
    {
        $this->get('/api/mis-eventos-json')
            ->assertRedirect(route('login'));
    }

    public function test_mis_eventos_json_retorna_auth_id_del_usuario_autenticado(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->getJson('/api/mis-eventos-json')
            ->assertOk()
            ->assertJsonPath('auth_id', $user->id);
    }

    public function test_events_by_date_requiere_autenticacion(): void
    {
        $this->getJson('/api/events/2026-03-15')->assertUnauthorized();
    }

    public function test_events_by_date_retorna_solo_los_eventos_del_dia(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory(2)->create(['user_id' => $user->id, 'date' => '2026-03-15']);
        Event::factory()->create(['user_id' => $user->id, 'date' => '2026-03-16']);

        $response = $this->actingAs($user)->getJson('/api/events/2026-03-15');

        $response->assertOk();
        $this->assertCount(2, $response->json());
    }

    public function test_events_by_date_retorna_array_vacio_para_fecha_sin_eventos(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)
            ->getJson('/api/events/2030-06-15')
            ->assertOk()
            ->assertJson([]);
    }

    public function test_admin_maicao_no_ve_eventos_riohacha_en_calendario(): void
    {
        [$maicao, $riohacha] = $this->createCampuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        Event::factory()->create([
            'title' => 'Evento Maicao',
            'user_id' => $admin->id,
            'campus_id' => $maicao->id,
            'date' => '2026-03-15',
        ]);
        Event::factory()->create([
            'title' => 'Evento Riohacha',
            'user_id' => $admin->id,
            'campus_id' => $riohacha->id,
            'date' => '2026-03-15',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/events/2026-03-15');
        $titles = collect($response->json())->pluck('title')->all();

        $this->assertContains('Evento Maicao', $titles);
        $this->assertNotContains('Evento Riohacha', $titles);
    }

    public function test_superadmin_sin_sede_ve_todos_los_eventos_del_calendario(): void
    {
        [$maicao, $riohacha] = $this->createCampuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create([
            'title' => 'Evento Maicao',
            'user_id' => $superadmin->id,
            'campus_id' => $maicao->id,
            'date' => '2026-03-15',
        ]);
        Event::factory()->create([
            'title' => 'Evento Riohacha',
            'user_id' => $superadmin->id,
            'campus_id' => $riohacha->id,
            'date' => '2026-03-15',
        ]);

        $response = $this->actingAs($superadmin)->getJson('/api/events/2026-03-15');
        $titles = collect($response->json())->pluck('title')->all();

        $this->assertContains('Evento Maicao', $titles);
        $this->assertContains('Evento Riohacha', $titles);
    }

    public function test_superadmin_con_sede_riohacha_solo_ve_riohacha_en_calendario(): void
    {
        [$maicao, $riohacha] = $this->createCampuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create([
            'title' => 'Evento Maicao',
            'user_id' => $superadmin->id,
            'campus_id' => $maicao->id,
            'date' => '2026-03-15',
        ]);
        Event::factory()->create([
            'title' => 'Evento Riohacha',
            'user_id' => $superadmin->id,
            'campus_id' => $riohacha->id,
            'date' => '2026-03-15',
        ]);

        $response = $this
            ->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/events/2026-03-15');
        $titles = collect($response->json())->pluck('title')->all();

        $this->assertNotContains('Evento Maicao', $titles);
        $this->assertContains('Evento Riohacha', $titles);
    }

    public function test_usuario_no_puede_abrir_detalle_de_evento_de_otra_sede(): void
    {
        [$maicao, $riohacha] = $this->createCampuses();
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
        ]);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'campus_id' => $riohacha->id,
            'date' => '2026-03-15',
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertForbidden();
    }

    public function test_usuario_puede_abrir_desde_el_calendario_un_evento_de_su_dependencia(): void
    {
        [$maicao, $riohacha] = $this->createCampuses();
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'campus_id' => null,
        ]);
        $creator = User::factory()->create([
            'role' => User::ROLE_USER,
            'campus_id' => $maicao->id,
        ]);
        $dependency = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $user->dependencies()->attach($dependency);
        $event = Event::factory()->create([
            'user_id' => $creator->id,
            'dependency_id' => $dependency->id,
            'campus_id' => $riohacha->id,
            'date' => '2026-03-15',
        ]);

        $this->actingAs($user)
            ->getJson('/api/events/2026-03-15')
            ->assertOk()
            ->assertJsonPath('0.id', $event->id)
            ->assertJsonPath('0.can_view', true)
            ->assertJsonPath('0.is_dependency_event', true)
            ->assertJsonPath('0.show_url', route('events.show', ['id' => $event->id, 'from' => 'calendario']));

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk();
    }

    public function test_ruta_publica_por_slug_sigue_funcionando(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'link' => 'evento-publico',
            'date' => '2026-03-15',
        ]);

        $this->get(route('events.access', $event->link))->assertOk();
    }

    private function createCampuses(): array
    {
        return [
            Campus::create(['name' => 'Maicao']),
            Campus::create(['name' => 'Riohacha']),
        ];
    }
}
