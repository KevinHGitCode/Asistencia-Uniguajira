<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contenedor reutilizable de grupos de eventos con búsqueda integrada (ADR-0012).
 */
class EventsGroupComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_de_eventos_usa_el_contenedor_con_buscador(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Conferencia de Prueba',
            'date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('events.list'));

        $response->assertOk();
        $response->assertSee('data-event-card', false);        // tarjeta reutilizable
        $response->assertSee('Buscar en este grupo', false);   // buscador integrado
        $response->assertSee('Conferencia de Prueba');
    }

    public function test_el_contenedor_expone_datos_y_boton_de_filtros(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Evento Filtrable',
            'date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('events.list'));

        $response->assertOk();
        $response->assertSee('data-status=', false);  // estado para filtro de cliente
        $response->assertSee('data-date=', false);     // fecha para rango
        $response->assertSee('Filtros', false);        // botón de filtros estructurados
    }

    public function test_information_enlaza_eventos_con_origen_usuario(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $target = User::factory()->create();
        Event::factory()->create([
            'user_id' => $target->id,
            'title' => 'Evento Del Usuario',
        ]);

        $response = $this->actingAs($admin)->get(route('users.information', $target->id));

        $response->assertOk();
        $response->assertSee('Evento Del Usuario');
        $response->assertSee('data-event-card', false);
        // El enlace conserva el origen para el breadcrumb (ADR-0013).
        $response->assertSee('from=usuario', false);
        $response->assertSee('user_id='.$target->id, false);
    }
}
