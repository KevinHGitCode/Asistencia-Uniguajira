<?php

namespace Tests\Feature\Users;

use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────
    //  Show (GET /usuarios/{id})
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login_en_show(): void
    {
        $user = User::factory()->create();

        $this->get(route('user.show', $user->id))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403_en_show(): void
    {
        $actor  = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create();

        $this->actingAs($actor)
            ->get(route('user.show', $target->id))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_show_de_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('user.show', $user->id))
            ->assertOk()
            ->assertViewIs('users.show');
    }

    public function test_show_pasa_el_usuario_correcto_a_la_vista(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['name' => 'Usuario Esperado']);

        $this->actingAs($admin)
            ->get(route('user.show', $user->id))
            ->assertViewHas('user', function ($viewUser) use ($user) {
                return $viewUser->id === $user->id;
            });
    }

    public function test_show_retorna_404_para_usuario_inexistente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('user.show', 99999))
            ->assertNotFound();
    }

    public function test_show_muestra_enlaces_visuales_de_edicion_e_informacion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('user.show', $user->id))
            ->assertOk()
            ->assertSee(route('user.edit', $user->id), false)
            ->assertSee(route('users.information', $user->id), false)
            ->assertSee('Editar');
    }

    // ─────────────────────────────────────────────
    //  Information (GET /usuarios/{id}/information)
    // ─────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login_en_information(): void
    {
        $user = User::factory()->create();

        $this->get(route('users.information', $user->id))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_regular_recibe_403_en_information(): void
    {
        $actor  = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create();

        $this->actingAs($actor)
            ->get(route('users.information', $target->id))
            ->assertForbidden();
    }

    public function test_admin_puede_ver_information_de_usuario(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.information', $user->id))
            ->assertOk()
            ->assertViewIs('users.information');
    }

    public function test_information_pasa_el_usuario_correcto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['name' => 'Juan Pérez']);

        $this->actingAs($admin)
            ->get(route('users.information', $user->id))
            ->assertViewHas('user', function ($viewUser) use ($user) {
                return $viewUser->id === $user->id;
            });
    }

    public function test_information_incluye_conteos_de_eventos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.information', $user->id))
            ->assertViewHas('eventsCount')
            ->assertViewHas('upcomingEvents')
            ->assertViewHas('pastEvents');
    }

    public function test_information_incluye_eventos_de_dependencias(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.information', $user->id))
            ->assertViewHas('dependencyEvents');
    }

    public function test_events_count_refleja_eventos_propios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();
        Event::factory(3)->create([
            'user_id' => $user->id,
            'link'    => 'https://example.com/event',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.information', $user->id));

        $response->assertViewHas('eventsCount', 3);
    }

    public function test_upcoming_events_count_es_correcto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        // 2 eventos futuros
        Event::factory(2)->create([
            'user_id' => $user->id,
            'date'    => now()->addDays(5)->toDateString(),
            'link'    => 'https://example.com/futuro',
        ]);
        // 1 evento pasado
        Event::factory(1)->create([
            'user_id' => $user->id,
            'date'    => now()->subDays(5)->toDateString(),
            'link'    => 'https://example.com/pasado',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.information', $user->id));

        $response->assertViewHas('upcomingEvents', 2);
        $response->assertViewHas('pastEvents', 1);
    }

    public function test_information_retorna_404_para_usuario_inexistente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.information', 99999))
            ->assertNotFound();
    }

    public function test_dependency_events_se_agrupan_por_dependencia(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $user       = User::factory()->create();
        $dependency = Dependency::factory()->create();

        $user->dependencies()->attach($dependency);

        // Evento de la dependencia creado por otro usuario
        $otherUser = User::factory()->create();
        Event::factory(2)->create([
            'user_id'       => $otherUser->id,
            'dependency_id' => $dependency->id,
            'link'          => 'https://example.com/dependency',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.information', $user->id));

        $response->assertViewHas('dependencyEvents', function ($depEvents) use ($dependency) {
            return array_key_exists($dependency->id, $depEvents);
        });
    }
}
