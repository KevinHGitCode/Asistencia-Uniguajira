<?php

namespace Tests\Feature\Configuration;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ParticipantStagingImportTest extends TestCase
{
    use RefreshDatabase;

    private function csv(array $dataRows): UploadedFile
    {
        $header = 'Documento,Nombres,Apellidos,Tipo de Estamento,Correo,Programa o Dependencia,Tipo_progama,Vinculacion';

        return UploadedFile::fake()->createWithContent(
            'participantes.csv',
            implode("\n", array_merge([$header], $dataRows)),
        );
    }

    private function seedCatalogs(?Campus $campus = null): Campus
    {
        $campus ??= Campus::create(['name' => 'Maicao']);

        ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        Program::factory()->create([
            'name' => 'Ingenieria de sistemas',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
        ]);

        return $campus;
    }

    public function test_la_plantilla_no_solicita_sede_para_participantes(): void
    {
        $export = new \App\Exports\ParticipantTemplateExport;

        $this->assertNotContains('Sede', $export->headings());
        $this->assertCount(8, $export->headings());
        $this->assertCount(8, $export->array()[0]);
    }

    public function test_la_importacion_guarda_en_staging_sin_tocar_la_tabla_principal(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $file = $this->csv([
            '1001,Ana,Perez,Estudiante,ana@u.co,Ingenieria de sistemas,Pregrado,',
            '1002,Luis,Gomez,Estudiante,luis@u.co,Ingenieria de sistemas,Pregrado,',
            ',Sin,Documento,Estudiante,,Ingenieria de sistemas,Pregrado,',       // omitido: documento vacío
            '1003,Mal,Estamento,NoExiste,,Ingenieria de sistemas,Pregrado,',     // omitido: estamento inválido
        ]);

        $this->actingAs($admin)
            ->post(route('participants-import.import'), ['excel_file' => $file])
            ->assertRedirect();

        // Nada se tocó en la tabla principal todavía.
        $this->assertSame(0, Participant::count());

        $batch = ImportBatch::latest()->first();
        $this->assertNotNull($batch);
        $this->assertSame('en_revision', $batch->status);
        $this->assertSame(2, $batch->new_count);
        $this->assertSame(2, $batch->skipped_count);
        $this->assertSame(2, $batch->stagedParticipants()->where('status', 'nuevo')->count());
        $this->assertSame(2, $batch->stagedParticipants()->where('status', 'omitido')->count());
    }

    public function test_superadmin_sin_sede_activa_importa_participantes_globales_sin_requerir_sede(): void
    {
        $riohacha = $this->seedCatalogs(Campus::create(['name' => 'Riohacha']));
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);

        $this->actingAs($superadmin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '4001,Ana,Global,Estudiante,ana.global@u.co,Ingenieria de sistemas,Pregrado,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();

        $this->assertSame('en_revision', $batch->status);
        $this->assertSame(1, $batch->new_count);

        $role = $batch->stagedParticipants()->firstOrFail()->roles[0];
        $this->assertSame(
            $riohacha->id,
            (int) Program::findOrFail($role['program_id'])->campus_id,
        );
    }

    public function test_import_reconoce_programa_con_guion_pegado_a_la_sede(): void
    {
        $campus = Campus::create(['name' => 'Villanueva']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);
        ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        $program = Program::create([
            'name' => 'Licenciatura en educación básica primaria - Villanueva',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
        ]);

        $this->actingAs($admin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '5001,Alba,Diaz,Estudiante,alba@u.co,LICENCIATURA EN EDUCACION BASICA PRIMARIA -VILLANUEVA,Pregrado,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame(1, $batch->new_count);
        $this->assertSame(0, $batch->skipped_count);
        $this->assertSame($program->id, $batch->stagedParticipants()->firstOrFail()->roles[0]['program_id']);
    }

    public function test_import_reconoce_programa_historico_sin_sufijo_cuando_el_valor_trae_sufijo_de_sede(): void
    {
        $campus = Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);
        ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        $program = Program::create([
            'name' => 'Licenciatura en música',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
        ]);

        $this->actingAs($admin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '5002,Abel,Medina,Estudiante,abel@u.co,LICENCIATURA EN MÚSICA - RIOHACHA,Pregrado,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame(1, $batch->new_count);
        $this->assertSame($program->id, $batch->stagedParticipants()->firstOrFail()->roles[0]['program_id']);
    }

    public function test_superadmin_usa_el_sufijo_del_programa_para_resolver_su_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        Program::create([
            'name' => 'Licenciatura en música',
            'program_type' => 'Pregrado',
            'campus_id' => $maicao->id,
        ]);
        $riohachaProgram = Program::create([
            'name' => 'Licenciatura en música',
            'program_type' => 'Pregrado',
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($superadmin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '5003,Abel,Medina,Estudiante,abel.rio@u.co,LICENCIATURA EN MÚSICA - RIOHACHA,Pregrado,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame(1, $batch->new_count);
        $this->assertSame($riohachaProgram->id, $batch->stagedParticipants()->firstOrFail()->roles[0]['program_id']);
    }

    public function test_import_reconoce_dependencia_con_sufijo_de_sede_en_el_excel(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);
        ParticipantType::firstOrCreate(['name' => 'Administrativo']);
        $dependency = Dependency::create([
            'name' => 'Coordinacion seguridad y salud en el trabajo - Maicao',
            'campus_id' => $campus->id,
        ]);

        $this->actingAs($admin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '6001,Disney,Solano,Administrativo,disney@u.co,COORDINACION SEGURIDAD Y SALUD EN EL TRABAJO - MAICAO,,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame(1, $batch->new_count);
        $this->assertSame(0, $batch->skipped_count);
        $this->assertSame($dependency->id, $batch->stagedParticipants()->firstOrFail()->roles[0]['dependency_id']);
    }

    public function test_import_indica_cuando_una_dependencia_existente_requiere_sufijo_de_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        ParticipantType::firstOrCreate(['name' => 'Administrativo']);

        Dependency::create(['name' => 'Biblioteca', 'campus_id' => $maicao->id]);
        Dependency::create(['name' => 'Biblioteca', 'campus_id' => $riohacha->id]);

        $this->actingAs($superadmin)
            ->post(route('participants-import.import'), [
                'excel_file' => $this->csv([
                    '7001,Brenda,Brito,Administrativo,brenda@u.co,BIBLIOTECA,,',
                ]),
            ])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame(0, $batch->new_count);
        $this->assertSame(1, $batch->skipped_count);
        $this->assertSame(
            'No se pudo determinar la sede de la dependencia "BIBLIOTECA". Agrega el sufijo "- Sede" en Programa o Dependencia.',
            $batch->stagedParticipants()->firstOrFail()->error,
        );
    }

    public function test_aprobar_requiere_la_contrasena_del_admin(): void
    {
        // El cast 'hashed' del modelo hashea la contraseña en texto plano.
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id, 'password' => 'secret-123']);

        $this->actingAs($admin)->post(route('participants-import.import'), [
            'excel_file' => $this->csv(['2001,Ana,Perez,Estudiante,ana2@u.co,Ingenieria de sistemas,Pregrado,']),
        ]);
        $batch = ImportBatch::latest()->first();

        // Contraseña incorrecta → no aplica nada.
        $this->actingAs($admin)
            ->post(route('participants-import.approve', $batch), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');
        $this->assertSame(0, Participant::count());
        $this->assertSame('en_revision', $batch->fresh()->status);

        // Contraseña correcta → aplica.
        $this->actingAs($admin)
            ->post(route('participants-import.approve', $batch), ['password' => 'secret-123'])
            ->assertRedirect(route('participants-import.index'));
        $this->assertSame(1, Participant::count());
        $this->assertDatabaseHas('participants', ['document' => '2001']);
        $this->assertSame('aprobado', $batch->fresh()->status);
        $this->assertNotNull($batch->fresh()->applied_at);
    }

    public function test_rechazar_no_crea_participantes(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $this->actingAs($admin)->post(route('participants-import.import'), [
            'excel_file' => $this->csv(['3001,Ana,Perez,Estudiante,ana3@u.co,Ingenieria de sistemas,Pregrado,']),
        ]);
        $batch = ImportBatch::latest()->first();

        $this->actingAs($admin)
            ->post(route('participants-import.reject', $batch))
            ->assertRedirect(route('participants-import.index'));

        $this->assertSame(0, Participant::count());
        $this->assertSame('rechazado', $batch->fresh()->status);
    }

    public function test_se_pueden_descargar_los_omitidos_de_un_lote_ya_procesado(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $this->actingAs($admin)->post(route('participants-import.import'), [
            'excel_file' => $this->csv([',Sin,Documento,Estudiante,,Ingenieria de sistemas,Pregrado,']),
        ]);
        $batch = ImportBatch::latest()->first();
        $this->assertSame(1, $batch->skipped_count);

        $response = $this->actingAs($admin)->get(route('participants-import.batch-skipped', $batch));
        $response->assertOk();
        $this->assertStringContainsString(
            'omitidos_lote_',
            (string) $response->headers->get('content-disposition'),
        );
    }
}
