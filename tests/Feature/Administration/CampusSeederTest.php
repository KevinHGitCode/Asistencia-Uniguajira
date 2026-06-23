<?php

namespace Tests\Feature\Administration;

use App\Models\Campus;
use Database\Seeders\CampusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_el_catalogo_inicial_de_sedes_y_es_idempotente(): void
    {
        $this->seed(CampusSeeder::class);
        $this->seed(CampusSeeder::class);

        $this->assertSame([
            'Albania',
            'Fonseca',
            'Maicao',
            'Manaure',
            'Monteria',
            'Riohacha',
            'Uribia',
            'Villanueva',
        ], Campus::orderBy('name')->pluck('name')->all());
    }
}
