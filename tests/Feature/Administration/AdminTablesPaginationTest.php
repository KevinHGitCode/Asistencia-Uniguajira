<?php

namespace Tests\Feature\Administration;

use App\Livewire\Administration\AffiliationTable;
use App\Livewire\Administration\OrganizationTable;
use App\Livewire\Administration\ParticipantTypeTable;
use App\Livewire\Administration\ProgramTable;
use App\Models\ActivityLog;
use App\Models\Affiliation;
use App\Models\Organization;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTablesPaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'email_verified_at' => now(),
        ]);
    }

    #[DataProvider('adminTableProvider')]
    public function test_la_vista_padre_usa_livewire_y_no_precarga_la_tabla(
        string $routeName,
        string $componentClass,
        string $modelClass,
        string $totalViewKey,
        string $legacyViewKey,
        string $componentViewKey
    ): void {
        $this->seedRecords($modelClass, 30);

        $this->actingAs($this->admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertSeeLivewire($componentClass)
            ->assertViewHas($totalViewKey, 30)
            ->assertViewMissing($legacyViewKey);
    }

    #[DataProvider('adminTableProvider')]
    public function test_la_tabla_ajusta_paginas_fuera_de_rango(
        string $routeName,
        string $componentClass,
        string $modelClass,
        string $totalViewKey,
        string $legacyViewKey,
        string $componentViewKey
    ): void {
        $this->seedRecords($modelClass, 240);

        Livewire::actingAs($this->admin)
            ->test($componentClass)
            ->call('gotoPage', 12)
            ->assertViewHas($componentViewKey, fn ($items) => $items->currentPage() === 10 && $items->lastPage() === 10 && $items->count() === 15);
    }

    public static function adminTableProvider(): array
    {
        return [
            'programs' => [
                'routeName' => 'programs.index',
                'componentClass' => ProgramTable::class,
                'modelClass' => Program::class,
                'totalViewKey' => 'totalPrograms',
                'legacyViewKey' => 'programs',
                'componentViewKey' => 'programs',
            ],
            'affiliations' => [
                'routeName' => 'affiliations.index',
                'componentClass' => AffiliationTable::class,
                'modelClass' => Affiliation::class,
                'totalViewKey' => 'totalAffiliations',
                'legacyViewKey' => 'affiliations',
                'componentViewKey' => 'affiliations',
            ],
            'organizations' => [
                'routeName' => 'organizations.index',
                'componentClass' => OrganizationTable::class,
                'modelClass' => Organization::class,
                'totalViewKey' => 'totalOrganizations',
                'legacyViewKey' => 'organizations',
                'componentViewKey' => 'organizations',
            ],
            'participant-types' => [
                'routeName' => 'participant-types.index',
                'componentClass' => ParticipantTypeTable::class,
                'modelClass' => ParticipantType::class,
                'totalViewKey' => 'totalParticipantTypes',
                'legacyViewKey' => 'participantTypes',
                'componentViewKey' => 'participantTypes',
            ],
        ];
    }

    public function test_registros_renderiza_la_paginacion_con_el_mismo_estilo(): void
    {
        foreach (range(1, 30) as $i) {
            ActivityLog::create([
                'action' => 'crear',
                'module' => 'programas',
                'description' => sprintf('Registro %03d', $i),
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('Listado de Registros')
            ->assertSee('name="module"', false)
            ->assertSee('name="action"', false)
            ->assertSee('name="user_id"', false)
            ->assertSee('Mostrando')
            ->assertSee('Siguiente');
    }

    public function test_registros_de_actividad_filtra_por_modulo_accion_y_usuario(): void
    {
        $otherUser = User::factory()->create(['name' => 'Otro usuario']);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'crear',
            'module' => 'programas',
            'description' => 'Registro filtrable esperado',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $otherUser->id,
            'action' => 'eliminar',
            'module' => 'dependencias',
            'description' => 'Registro que no debe aparecer',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('activity-logs.index', [
                'module' => 'programas',
                'action' => 'crear',
                'user_id' => $this->admin->id,
            ]))
            ->assertOk()
            ->assertSee('Registro filtrable esperado')
            ->assertDontSee('Registro que no debe aparecer')
            ->assertSee('programas', false)
            ->assertSee('crear', false)
            ->assertSee((string) $this->admin->id, false);
    }

    private function seedRecords(string $modelClass, int $count): void
    {
        foreach (range(1, $count) as $i) {
            match ($modelClass) {
                Program::class => Program::create([
                    'name' => sprintf('Programa %03d', $i),
                    'program_type' => 'Pregrado',
                ]),
                Affiliation::class => Affiliation::create([
                    'name' => sprintf('Afiliacion %03d', $i),
                ]),
                Organization::class => Organization::create([
                    'name' => sprintf('Organizacion %03d', $i),
                ]),
                ParticipantType::class => ParticipantType::create([
                    'name' => sprintf('Estamento %03d', $i),
                ]),
            };
        }
    }
}
