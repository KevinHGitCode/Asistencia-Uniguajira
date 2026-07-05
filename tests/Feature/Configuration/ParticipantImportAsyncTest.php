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
use Illuminate\Support\Facades\Schema;
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

    public function test_la_revision_de_un_lote_procesando_no_consulta_staging(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => Campus::create(['name' => 'X'])->id]);
        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'grande.xlsx',
            'status' => 'procesando',
        ]);

        Schema::dropIfExists('staged_participants');

        $this->actingAs($admin)
            ->get(route('participants-import.review', $batch))
            ->assertOk()
            ->assertSee('Procesando el archivo', false);
    }

    public function test_panel_de_participantes_avisa_si_hay_un_excel_en_proceso(): void
    {
        $campus = $this->seedCatalogs();
        $uploader = User::factory()->create([
            'name' => 'Admin Que Sube',
            'role' => 'admin',
            'campus_id' => $campus->id,
        ]);
        $viewer = User::factory()->create([
            'role' => 'admin',
            'campus_id' => $campus->id,
        ]);

        $batch = ImportBatch::create([
            'user_id' => $uploader->id,
            'original_filename' => 'participantes-grande.xlsx',
            'status' => 'procesando',
        ]);

        $this->actingAs($viewer)
            ->get(route('participants-import.index'))
            ->assertOk()
            ->assertSee('Hay una carga masiva de participantes en proceso')
            ->assertSee('participantes-grande.xlsx')
            ->assertSee('Admin Que Sube')
            ->assertSee('statusUrls', false)
            ->assertSee('Esta p&aacute;gina se actualizar&aacute; sola al terminar', false)
            ->assertSee('La carga est&aacute; deshabilitada', false);
    }

    public function test_no_permite_subir_otro_excel_mientras_un_lote_esta_procesando(): void
    {
        Queue::fake();
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'ya-procesando.xlsx',
            'status' => 'procesando',
        ]);

        $file = UploadedFile::fake()->createWithContent('participantes.csv', $this->csvContent());

        $this->actingAs($admin)
            ->post(route('participants-import.import'), ['excel_file' => $file])
            ->assertRedirect(route('participants-import.index'))
            ->assertSessionHas('error', 'Ya hay una carga masiva de participantes procesándose. Espera a que termine antes de subir otro Excel.');

        $this->assertSame(1, ImportBatch::count());
        Queue::assertNothingPushed();
    }

    public function test_panel_de_participantes_muestra_fecha_y_hora_de_lotes_pendientes(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        // `created_at` no es fillable en ImportBatch, así que se fija el "ahora"
        // de prueba para que la fecha del lote sea determinista (si no, tomaría
        // la fecha real del día y el test sería frágil).
        \Illuminate\Support\Carbon::setTestNow('2026-07-02 14:35:00');
        ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'ultim csr.xlsx',
            'status' => 'en_revision',
            'new_count' => 0,
            'update_count' => 52225,
            'skipped_count' => 636,
        ]);
        \Illuminate\Support\Carbon::setTestNow();

        $response = $this->actingAs($admin)
            ->get(route('participants-import.index'))
            ->assertOk()
            ->assertSee('Tienes lotes de importación pendientes de revisión')
            ->assertSee('ultim csr.xlsx')
            ->assertSee('Cargado el')
            ->assertSee('0 nuevos · 52225 actualizan · 636 omitidos');

        $this->assertMatchesRegularExpression(
            '/Cargado el\s+02\/07\/2026\s+\d{2}:\d{2}/',
            strip_tags($response->getContent()),
        );
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

    public function test_el_job_no_falla_si_no_existe_la_tabla_de_notificaciones(): void
    {
        $campus = $this->seedCatalogs();
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_filename' => 'grande.csv',
            'status' => 'procesando',
        ]);

        Storage::disk('local')->put('imports/job-sin-notificaciones.csv', $this->csvContent());
        Schema::dropIfExists('notifications');

        $ctx = ImportContext::fromUser($admin->fresh(), $campus->id);
        (new ParseParticipantImportJob($batch, 'local', 'imports/job-sin-notificaciones.csv', 'csv', $ctx))
            ->handle(app(ParticipantImportParser::class));

        $batch->refresh();
        $this->assertSame('en_revision', $batch->status);
        $this->assertSame(1, $batch->new_count);
        Storage::disk('local')->assertMissing('imports/job-sin-notificaciones.csv');
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
