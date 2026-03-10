<?php

namespace Tests\Feature;

use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de rendimiento para las páginas principales de la aplicación.
 *
 * Valida:
 *  1. Que cada página responde con HTTP 200.
 *  2. Que el tiempo de respuesta es aceptable (< 500 ms en SQLite local).
 *
 * Causas históricas de lentitud identificadas:
 *  - /usuarios: $search no aplicado a la query + ->get() cargaba todos los usuarios.
 *  - /administracion, /settings/profile: contención de escritura en SQLite por
 *    el queue worker (QUEUE_CONNECTION=database). En tests no hay worker, por lo
 *    que deben responder en < 100 ms.
 */
class PagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_MS = 500;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        // Dataset realista: dependencias, usuarios y eventos
        $dependencies = Dependency::factory()->count(5)->create();

        $users = User::factory()->count(20)->create();

        Event::factory()
            ->count(15)
            ->recycle($users)
            ->create();
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /**
     * Ejecuta un GET web y devuelve [response, tiempo_ms].
     *
     * El primer GET calienta la compilación Blade (cold-start); el segundo
     * mide el tiempo real con la plantilla ya cacheada, que es lo relevante.
     */
    private function timedGet(string $url): array
    {
        $this->get($url); // warm-up: compila y cachea la vista Blade

        $start    = microtime(true);
        $response = $this->get($url);
        $ms       = (microtime(true) - $start) * 1000;

        return [$response, (int) round($ms)];
    }

    // ── /usuarios ─────────────────────────────────────────────────────────────

    #[Test]
    public function usuarios_responde_ok_y_rapido(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/usuarios');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/usuarios tardó {$ms}ms (máx " . self::MAX_MS . "ms)");
    }

    #[Test]
    public function usuarios_con_busqueda_responde_ok_y_rapido(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/usuarios?q=admin');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/usuarios?q=admin tardó {$ms}ms");
    }

    #[Test]
    public function usuarios_paginacion_segunda_pagina_responde_ok(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/usuarios?page=2');

        // Puede ser 200 (si hay más de 20 usuarios) o redirigir a la 1ra página
        $this->assertTrue(
            $res->isOk() || $res->isRedirection(),
            "Esperaba 200 o redirección, obtuvo {$res->getStatusCode()}"
        );
        $this->assertLessThan(self::MAX_MS, $ms,
            "/usuarios?page=2 tardó {$ms}ms");
    }

    // ── /administracion ───────────────────────────────────────────────────────

    #[Test]
    public function administracion_responde_ok_y_rapido(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/administracion');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/administracion tardó {$ms}ms (máx " . self::MAX_MS . "ms)");
    }

    // ── /settings/profile ────────────────────────────────────────────────────

    #[Test]
    public function settings_profile_responde_ok_y_rapido(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/settings/profile');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/settings/profile tardó {$ms}ms (máx " . self::MAX_MS . "ms)");
    }

    // ── /dashboard ────────────────────────────────────────────────────────────

    #[Test]
    public function dashboard_responde_ok_y_rapido(): void
    {
        $this->actingAs($this->admin);
        [$res, $ms] = $this->timedGet('/dashboard');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/dashboard tardó {$ms}ms (máx " . self::MAX_MS . "ms)");
    }

    #[Test]
    public function dashboard_usuario_normal_responde_ok_y_rapido(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        [$res, $ms] = $this->timedGet('/dashboard');

        $res->assertOk();
        $this->assertLessThan(self::MAX_MS, $ms,
            "/dashboard (user) tardó {$ms}ms");
    }
}
