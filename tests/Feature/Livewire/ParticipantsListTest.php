<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\ParticipantsList;
use App\Models\Affiliation;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParticipantsListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un participante con exactamente un rol activo (sin usar el factory,
     * cuyo afterCreating añade roles aleatorios que romperían el determinismo).
     */
    private function participantWithRole(array $participant, array $role = []): Participant
    {
        $p = Participant::create(array_merge([
            'document'   => fake()->unique()->numerify('##########'),
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => null,
        ], $participant));

        ParticipantRole::create(array_merge([
            'participant_id' => $p->id,
            'is_active'      => true,
        ], $role));

        return $p;
    }

    public function test_el_selector_de_dependencias_muestra_la_sede_al_editar_un_participante(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $villanueva = Campus::create(['name' => 'Villanueva']);
        $maicaoDependency = Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $maicao->id]);
        $villanuevaDependency = Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $villanueva->id]);
        $participant = Participant::factory()->create();

        Livewire::test(ParticipantsList::class)
            ->call('openEdit', $participant->id)
            ->assertSet('catalogDependencies', [
                ['id' => $maicaoDependency->id, 'name' => 'Aseguramiento de la calidad - Maicao'],
                ['id' => $villanuevaDependency->id, 'name' => 'Aseguramiento de la calidad - Villanueva'],
            ]);
    }

    public function test_filtra_por_estamento(): void
    {
        $estudiante = ParticipantType::create(['name' => 'Estudiante']);
        $docente    = ParticipantType::create(['name' => 'Docente']);

        $this->participantWithRole(['document' => 'EST-001'], ['participant_type_id' => $estudiante->id]);
        $this->participantWithRole(['document' => 'DOC-002'], ['participant_type_id' => $docente->id]);

        Livewire::test(ParticipantsList::class)
            ->set('filterType', (string) $estudiante->id)
            ->assertSee('EST-001')
            ->assertDontSee('DOC-002');
    }

    public function test_filtra_por_programa(): void
    {
        $type     = ParticipantType::create(['name' => 'Estudiante']);
        $programA = Program::factory()->create(['name' => 'Ingeniería de Sistemas']);
        $programB = Program::factory()->create(['name' => 'Derecho']);

        $this->participantWithRole(['document' => 'PROG-AAA'], ['participant_type_id' => $type->id, 'program_id' => $programA->id]);
        $this->participantWithRole(['document' => 'PROG-BBB'], ['participant_type_id' => $type->id, 'program_id' => $programB->id]);

        Livewire::test(ParticipantsList::class)
            ->set('filterProgram', (string) $programA->id)
            ->assertSee('PROG-AAA')
            ->assertDontSee('PROG-BBB');
    }

    public function test_filtra_con_y_sin_correo(): void
    {
        $type = ParticipantType::create(['name' => 'Estudiante']);

        $this->participantWithRole(['document' => 'MAIL-SI', 'email' => 'con@correo.test'], ['participant_type_id' => $type->id]);
        $this->participantWithRole(['document' => 'MAIL-NO', 'email' => null], ['participant_type_id' => $type->id]);

        Livewire::test(ParticipantsList::class)
            ->set('filterEmail', 'con')
            ->assertSee('MAIL-SI')
            ->assertDontSee('MAIL-NO')
            ->set('filterEmail', 'sin')
            ->assertSee('MAIL-NO')
            ->assertDontSee('MAIL-SI');
    }

    public function test_los_filtros_de_rol_se_combinan_con_and(): void
    {
        $estudiante = ParticipantType::create(['name' => 'Estudiante']);
        $programA   = Program::factory()->create(['name' => 'Ingeniería de Sistemas']);
        $programB   = Program::factory()->create(['name' => 'Derecho']);

        // Mismo estamento, distinto programa.
        $this->participantWithRole(['document' => 'COMBO-OK'], ['participant_type_id' => $estudiante->id, 'program_id' => $programA->id]);
        $this->participantWithRole(['document' => 'COMBO-NO'], ['participant_type_id' => $estudiante->id, 'program_id' => $programB->id]);

        Livewire::test(ParticipantsList::class)
            ->set('filterType', (string) $estudiante->id)
            ->set('filterProgram', (string) $programA->id)
            ->assertSee('COMBO-OK')
            ->assertDontSee('COMBO-NO');
    }

    public function test_reset_filtros_limpia_todo(): void
    {
        $type        = ParticipantType::create(['name' => 'Estudiante']);
        $affiliation = Affiliation::create(['name' => 'Planta']);

        Livewire::test(ParticipantsList::class)
            ->set('search', 'algo')
            ->set('filterType', (string) $type->id)
            ->set('filterAffiliation', (string) $affiliation->id)
            ->set('filterEmail', 'con')
            ->set('filterUnclassified', true)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterType', '')
            ->assertSet('filterAffiliation', '')
            ->assertSet('filterEmail', '')
            ->assertSet('filterUnclassified', false);
    }
}
