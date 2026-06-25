<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\ParticipantsList;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El listado vive ahora en la isla React (ADR-0008); este componente Livewire
 * solo hospeda los modales de editar/eliminar, abiertos vía eventos del puente.
 */
class ParticipantsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_selector_de_dependencias_muestra_solo_el_nombre_guardado_al_editar_un_participante(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $villanueva = Campus::create(['name' => 'Villanueva']);
        $maicaoDependency = Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $maicao->id]);
        $villanuevaDependency = Dependency::create(['name' => 'Aseguramiento de la calidad', 'campus_id' => $villanueva->id]);
        $participant = Participant::factory()->create();

        Livewire::test(ParticipantsList::class)
            ->call('openEdit', $participant->id)
            ->assertSet('catalogDependencies', [
                ['id' => $maicaoDependency->id, 'name' => 'Aseguramiento de la calidad'],
                ['id' => $villanuevaDependency->id, 'name' => 'Aseguramiento de la calidad'],
            ]);
    }

    public function test_el_evento_open_edit_participant_abre_el_modal_de_edicion(): void
    {
        $participant = Participant::factory()->create();

        Livewire::test(ParticipantsList::class)
            ->assertSet('showEditModal', false)
            ->dispatch('open-edit-participant', id: $participant->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editingId', $participant->id);
    }

    public function test_el_evento_open_delete_participant_abre_el_modal_de_eliminacion(): void
    {
        $participant = Participant::factory()->create();
        $name = $participant->first_name.' '.$participant->last_name;

        Livewire::test(ParticipantsList::class)
            ->assertSet('showDeleteModal', false)
            ->dispatch('open-delete-participant', id: $participant->id, name: $name)
            ->assertSet('showDeleteModal', true)
            ->assertSet('deletingId', $participant->id)
            ->assertSet('deletingName', $name);
    }

    public function test_al_eliminar_emite_evento_para_refrescar_react(): void
    {
        $participant = Participant::factory()->create();

        Livewire::test(ParticipantsList::class)
            ->call('openDelete', $participant->id, 'X')
            ->call('deleteParticipant')
            ->assertDispatched('participants-refresh');
    }
}
