<?php

namespace Tests\Feature\Event;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventBreadcrumbContextTest extends TestCase
{
    use RefreshDatabase;

    private function campus(): Campus
    {
        return Campus::factory()->create();
    }

    private function adminWithEvent(): array
    {
        $campus = $this->campus();
        $dependency = Dependency::factory()->create(['campus_id' => $campus->id]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $campus->id,
        ]);
        $event = Event::factory()->create([
            'user_id' => $admin->id,
            'campus_id' => $campus->id,
            'dependency_id' => $dependency->id,
        ]);

        return [$admin, $event, $campus];
    }

    public function test_breadcrumb_por_defecto_muestra_eventos(): void
    {
        [$admin, $event] = $this->adminWithEvent();

        $response = $this->actingAs($admin)
            ->get(route('events.show', $event));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return $items[0]['label'] === 'Eventos'
                && $items[0]['route'] === 'events.list'
                && $items[1]['label'] === 'Información';
        });
    }

    public function test_breadcrumb_desde_mis_muestra_eventos(): void
    {
        [$admin, $event] = $this->adminWithEvent();

        $response = $this->actingAs($admin)
            ->get(route('events.show', [$event->id, 'from' => 'mis']));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return $items[0]['label'] === 'Eventos'
                && $items[0]['route'] === 'events.list';
        });
    }

    public function test_breadcrumb_desde_usuario_muestra_ruta_del_usuario(): void
    {
        [$admin, $event] = $this->adminWithEvent();
        $originUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $admin->campus_id,
            'name' => 'Ana García',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('events.show', [$event->id, 'from' => 'usuario', 'user_id' => $originUser->id]));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) use ($originUser) {
            return count($items) === 3
                && $items[0]['label'] === 'Usuarios'
                && $items[0]['route'] === 'users.index'
                && $items[1]['label'] === $originUser->name
                && $items[1]['route'] === 'users.information'
                && $items[1]['params'] === ['id' => $originUser->id]
                && $items[2]['label'] === 'Información';
        });
    }

    public function test_breadcrumb_desde_todos_muestra_ruta_admin(): void
    {
        [$admin, $event] = $this->adminWithEvent();

        $response = $this->actingAs($admin)
            ->get(route('events.show', [$event->id, 'from' => 'todos']));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return count($items) === 3
                && $items[0]['label'] === 'Dashboard'
                && $items[0]['route'] === 'dashboard'
                && $items[1]['label'] === 'Todos los eventos'
                && $items[1]['route'] === 'admin.events.index'
                && $items[2]['label'] === 'Información';
        });
    }

    public function test_from_invalido_cae_al_default(): void
    {
        [$admin, $event] = $this->adminWithEvent();

        $response = $this->actingAs($admin)
            ->get(route('events.show', [$event->id, 'from' => 'hack_injection']));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return $items[0]['label'] === 'Eventos'
                && $items[0]['route'] === 'events.list';
        });
    }

    public function test_from_usuario_sin_user_id_valido_cae_al_default(): void
    {
        [$admin, $event] = $this->adminWithEvent();

        $response = $this->actingAs($admin)
            ->get(route('events.show', [$event->id, 'from' => 'usuario', 'user_id' => 999999]));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return $items[0]['label'] === 'Eventos'
                && $items[0]['route'] === 'events.list';
        });
    }

    public function test_from_todos_para_usuario_sin_acceso_admin_cae_al_default(): void
    {
        [$admin, $event, $campus] = $this->adminWithEvent();
        $normalUser = User::factory()->create([
            'role' => User::ROLE_USER,
            'campus_id' => $campus->id,
        ]);
        $event->update(['user_id' => $normalUser->id]);

        $response = $this->actingAs($normalUser)
            ->get(route('events.show', [$event->id, 'from' => 'todos']));

        $response->assertOk();
        $response->assertViewHas('breadcrumbItems', function ($items) {
            return $items[0]['label'] === 'Eventos'
                && $items[0]['route'] === 'events.list';
        });
    }
}
