<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_have_multiple_programs_and_are_persisted(): void
    {
        $participant = Participant::create([
            'document'   => 'P-0001',
            'first_name' => 'Ana',
            'last_name'  => 'Perez',
            'email'      => 'ana.perez@example.com',
        ]);

        $programA = Program::create(['name' => 'Ingenieria', 'campus' => 'Riohacha']);
        $programB = Program::create(['name' => 'Derecho', 'campus' => 'Maicao']);

        $participant->programs()->syncWithoutDetaching([$programA->id, $programB->id]);

        $this->assertDatabaseHas('participant_program', [
            'participant_id' => $participant->id,
            'program_id'     => $programA->id,
        ]);
        $this->assertDatabaseHas('participant_program', [
            'participant_id' => $participant->id,
            'program_id'     => $programB->id,
        ]);

        $this->assertCount(2, $participant->programs()->get());
    }

    public function test_participant_can_have_multiple_types_and_affiliations(): void
    {
        $participant = Participant::create([
            'document'   => 'P-0002',
            'first_name' => 'Luis',
            'last_name'  => 'Gomez',
            'email'      => 'luis.gomez@example.com',
        ]);

        $typeA = ParticipantType::create(['name' => 'Estudiante']);
        $typeB = ParticipantType::create(['name' => 'Docente']);

        $affA = Affiliation::create(['name' => 'Planta']);
        $affB = Affiliation::create(['name' => 'Ocasional']);

        $participant->types()->syncWithoutDetaching([$typeA->id, $typeB->id]);
        $participant->affiliations()->syncWithoutDetaching([$affA->id, $affB->id]);

        $this->assertDatabaseHas('participant_type_participant', [
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeA->id,
        ]);
        $this->assertDatabaseHas('participant_type_participant', [
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeB->id,
        ]);

        $this->assertDatabaseHas('affiliation_participant', [
            'participant_id' => $participant->id,
            'affiliation_id' => $affA->id,
        ]);
        $this->assertDatabaseHas('affiliation_participant', [
            'participant_id' => $participant->id,
            'affiliation_id' => $affB->id,
        ]);

        $this->assertCount(2, $participant->types()->get());
        $this->assertCount(2, $participant->affiliations()->get());
    }
}
