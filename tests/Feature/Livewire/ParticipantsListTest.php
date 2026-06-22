<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\ParticipantsList;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ParticipantsListTest extends TestCase
{
    use RefreshDatabase;

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
}
