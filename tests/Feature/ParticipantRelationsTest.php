<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_have_multiple_roles_with_programs(): void
    {
        $participant = Participant::create([
            'document'   => 'P-0001',
            'first_name' => 'Ana',
            'last_name'  => 'Perez',
            'email'      => 'ana.perez@example.com',
        ]);

        $type     = ParticipantType::create(['name' => 'Estudiante']);
        $programA = Program::create(['name' => 'Ingenieria', 'campus' => 'Riohacha']);
        $programB = Program::create(['name' => 'Derecho', 'campus' => 'Maicao']);

        ParticipantRole::create([
            'participant_id'      => $participant->id,
            'participant_type_id' => $type->id,
            'program_id'          => $programA->id,
            'is_active'           => true,
        ]);

        ParticipantRole::create([
            'participant_id'      => $participant->id,
            'participant_type_id' => $type->id,
            'program_id'          => $programB->id,
            'is_active'           => true,
        ]);

        $this->assertDatabaseHas('participant_roles', [
            'participant_id' => $participant->id,
            'program_id'     => $programA->id,
        ]);
        $this->assertDatabaseHas('participant_roles', [
            'participant_id' => $participant->id,
            'program_id'     => $programB->id,
        ]);

        $this->assertCount(2, $participant->activeRoles);
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
        $affA  = Affiliation::create(['name' => 'Planta']);
        $affB  = Affiliation::create(['name' => 'Ocasional']);

        $programA = Program::create(['name' => 'Ingenieria', 'campus' => 'Riohacha']);

        ParticipantRole::create([
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeA->id,
            'program_id'          => $programA->id,
            'is_active'           => true,
        ]);

        ParticipantRole::create([
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeB->id,
            'program_id'          => $programA->id,
            'affiliation_id'      => $affA->id,
            'is_active'           => true,
        ]);

        ParticipantRole::create([
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeB->id,
            'program_id'          => $programA->id,
            'affiliation_id'      => $affB->id,
            'is_active'           => true,
        ]);

        $this->assertDatabaseHas('participant_roles', [
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeA->id,
        ]);
        $this->assertDatabaseHas('participant_roles', [
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeB->id,
            'affiliation_id'      => $affA->id,
        ]);
        $this->assertDatabaseHas('participant_roles', [
            'participant_id'      => $participant->id,
            'participant_type_id' => $typeB->id,
            'affiliation_id'      => $affB->id,
        ]);

        $this->assertCount(3, $participant->activeRoles);
        $types = $participant->activeRoles->pluck('type.name')->unique();
        $this->assertCount(2, $types);
    }
}