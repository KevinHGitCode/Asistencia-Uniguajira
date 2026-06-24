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
}
