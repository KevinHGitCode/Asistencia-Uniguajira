<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpia el estado acumulado del limitador entre pruebas (cache array).
        Cache::flush();
    }

    public function test_registro_publico_de_asistencia_devuelve_429_al_superar_el_limite(): void
    {
        config(['throttle.attendance' => 3]);

        $event = Event::factory()->create(['link' => 'evento-rate-test']);
        $url = route('attendance.store', ['slug' => $event->link]);

        // El throttle se evalúa antes del controlador: las primeras N peticiones
        // pasan el limitador (su status depende de la validación, no es 429).
        for ($i = 0; $i < 3; $i++) {
            $this->assertNotSame(429, $this->post($url, [])->getStatusCode());
        }

        // La siguiente supera el límite → 429 con cabecera Retry-After.
        $blocked = $this->post($url, []);
        $blocked->assertStatus(429);
        $blocked->assertHeader('Retry-After');
    }

    public function test_pagina_publica_de_acceso_devuelve_429_al_superar_el_limite(): void
    {
        config(['throttle.public' => 3]);

        $event = Event::factory()->create(['link' => 'acceso-rate-test']);
        $url = route('events.access', ['slug' => $event->link]);

        // El limitador cuenta cada petición sin importar el status de la respuesta.
        for ($i = 0; $i < 3; $i++) {
            $this->assertNotSame(429, $this->get($url)->getStatusCode());
        }

        $this->get($url)->assertStatus(429);
    }

    public function test_distinto_slug_no_comparte_el_limite_de_asistencia(): void
    {
        config(['throttle.attendance' => 2]);

        $eventoA = Event::factory()->create(['link' => 'evento-a']);
        $eventoB = Event::factory()->create(['link' => 'evento-b']);

        // Agota el límite del evento A.
        $this->post(route('attendance.store', ['slug' => $eventoA->link]), []);
        $this->post(route('attendance.store', ['slug' => $eventoA->link]), []);
        $this->post(route('attendance.store', ['slug' => $eventoA->link]), [])->assertStatus(429);

        // El evento B mantiene su propio cupo (clave IP + slug).
        $this->assertNotSame(
            429,
            $this->post(route('attendance.store', ['slug' => $eventoB->link]), [])->getStatusCode()
        );
    }
}
