<?php

namespace Tests\Feature\Api;

use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantsApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role'              => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
    }

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

    public function test_requiere_rol_administrador(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_USER]))
            ->getJson('/api/participants')
            ->assertForbidden();
    }

    public function test_devuelve_participantes_paginados_con_meta(): void
    {
        $type = ParticipantType::create(['name' => 'Estudiante']);
        foreach (range(1, 30) as $i) {
            $this->participantWithRole(
                ['document' => 'P-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)],
                ['participant_type_id' => $type->id],
            );
        }

        $response = $this->actingAs($this->admin())
            ->getJson('/api/participants?perPage=25')
            ->assertOk()
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 25)
            ->assertJsonPath('meta.last_page', 2);

        $this->assertCount(25, $response->json('data'));
    }

    public function test_filtra_por_estamento(): void
    {
        $estudiante = ParticipantType::create(['name' => 'Estudiante']);
        $docente    = ParticipantType::create(['name' => 'Docente']);

        $this->participantWithRole(['document' => 'EST-001'], ['participant_type_id' => $estudiante->id]);
        $this->participantWithRole(['document' => 'DOC-002'], ['participant_type_id' => $docente->id]);

        $this->actingAs($this->admin())
            ->getJson('/api/participants?estamento='.$estudiante->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['document' => 'EST-001'])
            ->assertJsonMissing(['document' => 'DOC-002']);
    }

    public function test_filtra_con_y_sin_correo(): void
    {
        $type = ParticipantType::create(['name' => 'Estudiante']);
        $this->participantWithRole(['document' => 'MAIL-SI', 'email' => 'con@correo.test'], ['participant_type_id' => $type->id]);
        $this->participantWithRole(['document' => 'MAIL-NO', 'email' => null], ['participant_type_id' => $type->id]);

        $this->actingAs($this->admin())
            ->getJson('/api/participants?correo=con')
            ->assertOk()
            ->assertJsonFragment(['document' => 'MAIL-SI'])
            ->assertJsonMissing(['document' => 'MAIL-NO']);

        $this->actingAs($this->admin())
            ->getJson('/api/participants?correo=sin')
            ->assertOk()
            ->assertJsonFragment(['document' => 'MAIL-NO'])
            ->assertJsonMissing(['document' => 'MAIL-SI']);
    }

    public function test_filter_options_devuelve_catalogos(): void
    {
        ParticipantType::create(['name' => 'Estudiante']);
        Program::factory()->create(['name' => 'Derecho']);

        $this->actingAs($this->admin())
            ->getJson('/api/participants/filter-options')
            ->assertOk()
            ->assertJsonStructure(['types', 'programs', 'affiliations', 'dependencies'])
            ->assertJsonFragment(['name' => 'Estudiante'])
            ->assertJsonFragment(['name' => 'Derecho']);
    }
}
