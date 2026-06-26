<?php

namespace Tests\Feature\Configuration;

use App\Jobs\ParseParticipantImportJob;
use App\Models\Campus;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use App\Notifications\ImportBatchReady;
use App\Services\ParticipantImportParser;
use App\Support\ImportContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantImportAsyncTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogs(): Campus
    {
        $campus = Campus::create(['name' => 'Maicao']);
        ParticipantType::firstOrCreate(['name' => 'Estudiante']);
        Program::factory()->create([
            'name' => 'Ingenieria de sistemas',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
        ]);

        return $campus;
    }

    private function csvContent(): string
    {
        return implode("\n", [
            'Documento,Nombres,Apellidos,Tipo de Estamento,Correo,Programa o Dependencia,Tipo_progama,Vinculacion',
            '1001,Ana,Perez,Estudiante,ana@u.co,Ingenieria de sistemas,Pregrado,',
        ]);
    }

    public function test_un_xlsx_grande_se_encola_y_deja_el_lote_procesando(): void
    {
        Queue::fake();
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        // 300 KB > umbral de 256 KB → debe encolarse.
        $file = UploadedFile::fake()->create(
            'grande.xlsx', 300,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->actingAs($admin)
            ->post(route('participants-import.import'), ['excel_file' => $file])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame('procesando', $batch->status);
        $this->assertSame(0, Participant::count());

        Queue::assertPushed(
            ParseParticipantImportJob::class,
            fn ($job) => $job->batch->id === $batch->id && $job->extension === 'xlsx',
        );
    }

    public function test_un_csv_se_procesa_inline_sin_encolar(): void
    {
        Queue::fake();
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $file = UploadedFile::fake()->createWithContent('participantes.csv', $this->csvContent());

        $this->actingAs($admin)
            ->post(route('participants-import.import'), ['excel_file' => $file])
            ->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertSame('en_revision', $batch->status);
        $this->assertSame(1, $batch->new_count);

        Queue::assertNothingPushed();
    }

    public function test_el_job_parsea_a_staging_y_notifica_al_terminar(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'grande.csv',
            'status' => 'procesando',
        ]);

        Storage::disk('local')->put('imports/job.csv', $this->csvContent());

        $ctx = ImportContext::fromUser($admin->fresh(), $campus->id);
        (new ParseParticipantImportJob($batch, 'local', 'imports/job.csv', 'csv', $ctx))
            ->handle(app(ParticipantImportParser::class));

        $batch->refresh();
        $this->assertSame('en_revision', $batch->status);
        $this->assertSame(1, $batch->new_count);

        // Aviso in-app al usuario que subió el lote (ADR-0018).
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => ImportBatchReady::class,
        ]);

        // El archivo temporal se limpia al terminar.
        Storage::disk('local')->assertMissing('imports/job.csv');
    }

    public function test_el_job_marca_error_cuando_el_archivo_esta_vacio(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => Campus::create(['name' => 'X'])->id]);

        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'vacio.csv',
            'status' => 'procesando',
        ]);

        Storage::disk('local')->put('imports/empty.csv', '');

        $ctx = ImportContext::fromUser($admin->fresh(), $admin->campus_id);
        (new ParseParticipantImportJob($batch, 'local', 'imports/empty.csv', 'csv', $ctx))
            ->handle(app(ParticipantImportParser::class));

        $batch->refresh();
        $this->assertSame('error', $batch->status);
        $this->assertNotNull($batch->error_message);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_el_endpoint_de_status_devuelve_el_estado_del_lote(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => Campus::create(['name' => 'X'])->id]);
        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'x.xlsx',
            'status' => 'procesando',
        ]);

        $this->actingAs($admin)
            ->getJson(route('participants-import.status', $batch))
            ->assertOk()
            ->assertJson(['status' => 'procesando']);
    }
}
