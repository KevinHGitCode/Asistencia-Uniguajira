<?php

namespace Tests\Feature\Administration;

use App\Models\AcademicProgram;
use App\Models\Campus;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ParticipantAcademicProgramRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mismo_participante_puede_tener_dos_roles_activos_del_mismo_programa_academico_en_sedes_distintas(): void
    {
        [$maicao, $riohacha, $academicProgram] = $this->campusesAndAcademicProgram();
        $participant = Participant::factory()->create();
        $type = ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        $maicaoProgram = Program::factory()->create([
            'campus_id' => $maicao->id,
            'academic_program_id' => $academicProgram->id,
        ]);
        $riohachaProgram = Program::factory()->create([
            'campus_id' => $riohacha->id,
            'academic_program_id' => $academicProgram->id,
        ]);

        ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $maicaoProgram->id,
            'is_active' => true,
        ]);

        ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $riohachaProgram->id,
            'is_active' => true,
        ]);

        $this->assertSame(2, $participant->activeRoles()
            ->whereIn('program_id', [$maicaoProgram->id, $riohachaProgram->id])
            ->count());
    }

    public function test_roles_inactivos_y_programas_academicos_distintos_pueden_coexistir(): void
    {
        [$maicao, $riohacha, $academicProgram] = $this->campusesAndAcademicProgram();
        $otherAcademicProgram = AcademicProgram::create(['name' => 'Derecho']);
        $participant = Participant::factory()->create();
        $type = ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        $inactiveProgram = Program::factory()->create([
            'campus_id' => $maicao->id,
            'academic_program_id' => $academicProgram->id,
        ]);
        $otherProgram = Program::factory()->create([
            'campus_id' => $riohacha->id,
            'academic_program_id' => $otherAcademicProgram->id,
        ]);

        ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $inactiveProgram->id,
            'is_active' => false,
        ]);

        ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $otherProgram->id,
            'is_active' => true,
        ]);

        $this->assertSame(1, $participant->activeRoles()
            ->where('program_id', $otherProgram->id)
            ->count());
        $this->assertDatabaseHas('participant_roles', [
            'participant_id' => $participant->id,
            'program_id' => $inactiveProgram->id,
            'is_active' => false,
        ]);
    }

    public function test_import_guarda_roles_del_mismo_programa_academico_en_sedes_distintas(): void
    {
        [$maicao, $riohacha, $academicProgram] = $this->campusesAndAcademicProgram();
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null, 'password' => 'secret-123']);
        $participant = Participant::factory()->create(['document' => '9001']);
        $type = ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        $maicaoProgram = Program::factory()->create([
            'name' => 'Ingenieria de sistemas - Maicao',
            'campus_id' => $maicao->id,
            'academic_program_id' => $academicProgram->id,
        ]);
        $riohachaProgram = Program::factory()->create([
            'name' => 'Ingenieria de sistemas - Riohacha',
            'campus_id' => $riohacha->id,
            'academic_program_id' => $academicProgram->id,
        ]);
        ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $maicaoProgram->id,
            'is_active' => true,
        ]);

        $csv = implode("\n", [
            'Documento,Nombres,Apellidos,Tipo de Estamento,Correo,Programa o Dependencia,Tipo_progama,Vinculacion',
            '9001,Ana,Perez,Estudiante,,Ingenieria de sistemas - Riohacha,Pregrado,',
        ]);

        $this->actingAs($superadmin)
            ->post(route('participants-import.import'), [
                'excel_file' => UploadedFile::fake()->createWithContent('participantes.csv', $csv),
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('staged_participants', [
            'status' => 'omitido',
            'error' => 'El participante ya tiene un rol activo para este programa académico en otra sede.',
        ]);

        $batch = ImportBatch::latest()->firstOrFail();

        $this->actingAs($superadmin)
            ->post(route('participants-import.approve', $batch), ['password' => 'secret-123'])
            ->assertRedirect(route('participants-import.index'));

        $this->assertDatabaseHas('participant_roles', [
            'participant_id' => $participant->id,
            'participant_type_id' => $type->id,
            'program_id' => $riohachaProgram->id,
            'is_active' => true,
        ]);
    }

    private function campusesAndAcademicProgram(): array
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $academicProgram = AcademicProgram::create(['name' => 'Ingenieria de sistemas']);

        return [$maicao, $riohacha, $academicProgram];
    }
}
