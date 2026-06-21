<?php

namespace Tests\Feature\Configuration;

use App\Models\ImportBatch;
use App\Models\Campus;
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
