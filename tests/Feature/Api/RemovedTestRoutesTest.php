<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * ADR-0014 — las "RUTAS DE PRUEBA" públicas sin auth de routes/api.php fueron
 * retiradas por filtrar toda la base de datos sin autenticación. Este test fija
 * el arreglo: deben responder 404 (no existir).
 */
class RemovedTestRoutesTest extends TestCase
{
    use RefreshDatabase;

    public static function removedRoutes(): array
    {
        return [
            ['/api/events'],
            ['/api/events-with-user'],
            ['/api/events/user/1'],
            ['/api/participants/program/1'],
            ['/api/participants/count-by-program'],
            ['/api/roles'],
            ['/api/programs'],
            ['/api/affiliations'],
            ['/api/attendances'],
            ['/api/users'],
            ['/api/dependencies'],
        ];
    }

    #[DataProvider('removedRoutes')]
    public function test_ruta_de_prueba_retirada_responde_404(string $uri): void
    {
        $this->getJson($uri)->assertNotFound();
    }

    public function test_rutas_legitimas_del_calendario_siguen_existiendo(): void
    {
        // /api/events/{date} (getByDate) NO es una ruta de prueba: existe y exige auth.
        $this->getJson('/api/events/2026-03-15')->assertUnauthorized();
    }

    public function test_ruta_legitima_de_participantes_sigue_existiendo(): void
    {
        // /api/participants alimenta la isla React de participantes y exige auth/admin.
        $this->getJson('/api/participants')->assertUnauthorized();
    }
}
