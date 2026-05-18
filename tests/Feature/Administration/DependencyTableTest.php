<?php

namespace Tests\Feature\Administration;

use App\Livewire\Administration\DependencyTable;
use App\Models\Dependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DependencyTableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'               => 'admin',
            'email_verified_at'  => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Acceso a la página
    // ─────────────────────────────────────────────────────────────────────────

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('dependencies.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_accede_a_la_pagina_de_dependencias(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dependencies.index'))
            ->assertOk()
            ->assertSeeLivewire(DependencyTable::class);
    }

    public function test_la_vista_padre_no_precarga_toda_la_coleccion_de_dependencias(): void
    {
        Dependency::factory()->count(30)->create();

        $this->actingAs($this->admin)
            ->get(route('dependencies.index'))
            ->assertOk()
            ->assertViewHas('totalDependencies', 30)
            ->assertViewMissing('dependencies');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Paginación — comportamiento
    // ─────────────────────────────────────────────────────────────────────────

    public function test_muestra_25_registros_por_pagina(): void
    {
        Dependency::factory()->count(30)->create();

        $component = Livewire::actingAs($this->admin)
            ->test(DependencyTable::class);

        // La primera página muestra exactamente 25 items
        $component->assertViewHas('dependencies', function ($deps) {
            return $deps->count() === 25 && $deps->perPage() === 25;
        });
    }

    public function test_pagina_inicial_es_la_primera(): void
    {
        Dependency::factory()->count(30)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 1);
    }

    public function test_nextPage_avanza_a_la_segunda_pagina(): void
    {
        Dependency::factory()->count(30)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('nextPage')
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 2);
    }

    public function test_previousPage_retrocede_desde_la_segunda_pagina(): void
    {
        Dependency::factory()->count(30)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('gotoPage', 2)
            ->call('previousPage')
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 1);
    }

    public function test_gotoPage_navega_a_pagina_especifica(): void
    {
        Dependency::factory()->count(80)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('gotoPage', 3)
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 3);
    }

    public function test_segunda_pagina_muestra_los_items_correctos(): void
    {
        // Crear 30 dependencias ordenadas por nombre de forma predecible
        foreach (range(1, 30) as $i) {
            Dependency::factory()->create(['name' => sprintf('Dependencia %02d', $i)]);
        }

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('nextPage')
            ->assertViewHas('dependencies', function ($deps) {
                // La segunda página tiene los ítems 26-30
                return $deps->count() === 5 && $deps->currentPage() === 2;
            });
    }

    public function test_ultima_pagina_desactiva_la_navegacion_hacia_adelante(): void
    {
        Dependency::factory()->count(30)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('gotoPage', 2)
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 2 && ! $deps->hasMorePages())
            ->assertSee('cursor-not-allowed');
    }

    public function test_pagina_fuera_de_rango_se_ajusta_a_la_ultima_pagina_disponible(): void
    {
        Dependency::factory()->count(240)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('gotoPage', 12)
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 10 && $deps->lastPage() === 10 && $deps->count() === 15);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Búsqueda
    // ─────────────────────────────────────────────────────────────────────────

    public function test_busqueda_filtra_por_nombre(): void
    {
        Dependency::factory()->create(['name' => 'Administración central']);
        Dependency::factory()->create(['name' => 'Biblioteca universitaria']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'Biblioteca')
            ->assertViewHas('dependencies', function ($deps) {
                return $deps->total() === 1
                    && $deps->first()->name === 'Biblioteca universitaria';
            });
    }

    public function test_busqueda_es_case_insensitive(): void
    {
        Dependency::factory()->create(['name' => 'Secretaría general']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'secretaría')
            ->assertViewHas('dependencies', fn ($deps) => $deps->total() >= 1);
    }

    public function test_busqueda_parcial_devuelve_coincidencias(): void
    {
        Dependency::factory()->create(['name' => 'Facultad de ingeniería']);
        Dependency::factory()->create(['name' => 'Facultad de medicina']);
        Dependency::factory()->create(['name' => 'Rectoría']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'Facultad')
            ->assertViewHas('dependencies', fn ($deps) => $deps->total() === 2);
    }

    public function test_busqueda_sin_resultados_muestra_total_cero(): void
    {
        Dependency::factory()->create(['name' => 'Administración central']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'xyz_no_existe_99999')
            ->assertViewHas('dependencies', fn ($deps) => $deps->total() === 0);
    }

    public function test_cambiar_busqueda_reinicia_a_pagina_1(): void
    {
        Dependency::factory()->count(30)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->call('gotoPage', 2)
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 2)
            ->set('search', 'algo')
            ->assertViewHas('dependencies', fn ($deps) => $deps->currentPage() === 1);
    }

    public function test_limpiar_busqueda_muestra_todos_los_registros(): void
    {
        Dependency::factory()->count(5)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'xyz')
            ->assertViewHas('dependencies', fn ($deps) => $deps->total() === 0)
            ->set('search', '')
            ->assertViewHas('dependencies', fn ($deps) => $deps->total() === 5);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Rendimiento — sin N+1
    // ─────────────────────────────────────────────────────────────────────────

    public function test_el_numero_de_queries_no_escala_con_el_numero_de_registros(): void
    {
        // Cargar con 25 registros (una página)
        Dependency::factory()->count(25)->create();

        $queriesWith25 = $this->countQueriesForComponentRender();

        // Añadir 25 más (ahora hay 50, dos páginas)
        Dependency::factory()->count(25)->create();

        $queriesWith50 = $this->countQueriesForComponentRender();

        // El conteo de queries debe ser idéntico: no crece con más registros (N+1 check)
        $this->assertEquals(
            $queriesWith25,
            $queriesWith50,
            "Se ejecutaron {$queriesWith50} queries con 50 registros vs {$queriesWith25} con 25. " .
            'El número de queries no debe crecer con más registros (posible N+1).'
        );
    }

    public function test_usa_withCount_para_evitar_queries_extra_por_relaciones(): void
    {
        Dependency::factory()->count(10)->create();

        $queries = $this->queriesForComponentRender();

        // Con withCount, todas las relaciones se resuelven en la query principal.
        // Esperamos máx. 4 queries: sesión/auth + count (paginator) + SELECT datos
        $this->assertLessThanOrEqual(
            4,
            count($queries),
            'Se esperaban máx. 4 queries. Encontradas: ' . count($queries) .
            '. Posible N+1 o carga innecesaria de relaciones.'
        );
    }

    public function test_paginacion_no_carga_mas_de_25_modelos_en_memoria(): void
    {
        Dependency::factory()->count(60)->create();

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->assertViewHas('dependencies', function ($deps) {
                // count() en un paginador devuelve solo los items de la página actual
                return $deps->count() === 25;
            });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Renderizado del componente
    // ─────────────────────────────────────────────────────────────────────────

    public function test_renderiza_la_vista_correcta(): void
    {
        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->assertViewIs('livewire.administration.dependency-table');
    }

    public function test_muestra_estado_vacio_cuando_no_hay_registros(): void
    {
        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->assertSee('No hay dependencias registradas aún.');
    }

    public function test_muestra_estado_vacio_con_mensaje_de_busqueda(): void
    {
        Dependency::factory()->create(['name' => 'Administración central']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->set('search', 'xyz_no_existe')
            ->assertSee('No se encontraron dependencias para');
    }

    public function test_los_nombres_de_dependencias_aparecen_en_la_tabla(): void
    {
        Dependency::factory()->create(['name' => 'Rectoría general']);
        Dependency::factory()->create(['name' => 'Vicerrectoría académica']);

        Livewire::actingAs($this->admin)
            ->test(DependencyTable::class)
            ->assertSee('Rectoría general')
            ->assertSee('Vicerrectoría académica');
    }

    public function test_la_numeracion_de_filas_continua_entre_paginas(): void
    {
        Dependency::factory()->count(30)->create();

        $component = Livewire::actingAs($this->admin)->test(DependencyTable::class);

        // En la primera página el offset es 0
        $component->assertViewHas('dependencies', function ($deps) {
            return $deps->currentPage() === 1 && ($deps->currentPage() - 1) * $deps->perPage() === 0;
        });

        // En la segunda página el offset es 25
        $component->call('nextPage')
            ->assertViewHas('dependencies', function ($deps) {
                return $deps->currentPage() === 2 && ($deps->currentPage() - 1) * $deps->perPage() === 25;
            });
    }

    private function countQueriesForComponentRender(): int
    {
        return count($this->queriesForComponentRender());
    }

    private function queriesForComponentRender(): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->admin)->test(DependencyTable::class);

        $queries = DB::getQueryLog();

        DB::disableQueryLog();
        DB::flushQueryLog();

        return $queries;
    }
}
