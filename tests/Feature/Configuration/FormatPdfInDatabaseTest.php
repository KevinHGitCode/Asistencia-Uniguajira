<?php

namespace Tests\Feature\Configuration;

use App\Models\Dependency;
use App\Models\Event;
use App\Models\Format;
use App\Models\User;
use App\Services\AttendancePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ADR-0017 — El PDF del formato se guarda en la BD para que sobreviva aunque se
 * borre la carpeta public/formats.
 */
class FormatPdfInDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
    }

    private function realPdf(): string
    {
        $pdfs = glob(public_path('formats').DIRECTORY_SEPARATOR.'*.pdf');
        if (empty($pdfs)) {
            $this->markTestSkipped('No hay un PDF real en public/formats para usar como fixture.');
        }

        return $pdfs[0];
    }

    public function test_subir_un_pdf_guarda_los_bytes_en_la_bd(): void
    {
        $realPdf = $this->realPdf();
        Storage::fake('formats');

        $file = new UploadedFile($realPdf, 'plantilla.pdf', 'application/pdf', null, true);

        $this->actingAs($this->superadmin())
            ->post(route('formats.store'), ['name' => 'Acta', 'slug' => 'acta', 'pdf_file' => $file])
            ->assertRedirect(route('formats.index'));

        $format = Format::where('slug', 'acta')->firstOrFail();

        $this->assertTrue($format->hasPdfInDb());
        $this->assertSame(file_get_contents($realPdf), $format->pdfContents());
        $this->assertDatabaseHas('format_files', [
            'format_id' => $format->id,
            'size' => filesize($realPdf),
        ]);
    }

    public function test_el_pdf_sobrevive_aunque_se_borre_la_carpeta(): void
    {
        $bytes = file_get_contents($this->realPdf());

        // Nada en disco: sólo la copia en BD.
        Storage::fake('formats');

        $format = Format::create(['name' => 'X', 'slug' => 'x']);
        $format->storePdf($bytes);

        $this->assertFalse(Storage::disk('formats')->exists('x.pdf'));
        $this->assertSame($bytes, $format->fresh()->pdfContents());
    }

    public function test_generar_pdf_usa_los_bytes_de_la_bd_sin_archivo_en_disco(): void
    {
        $bytes = file_get_contents($this->realPdf());

        $dependency = Dependency::factory()->create();
        $user = User::factory()->create();

        $format = Format::create([
            'name' => 'General',
            'slug' => 'general',
            'file' => 'inexistente_'.time().'.pdf', // este archivo NO existe en disco
            'mapping' => config('attendance_formats.general'),
        ]);
        $format->storePdf($bytes);

        $event = Event::create([
            'title' => 'Jornada de prueba',
            'description' => 'x',
            'date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'location' => 'Campus',
            'link' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'dependency_id' => $dependency->id,
        ]);

        // Aseguramos que no hay archivo en disco con ese nombre.
        $this->assertFalse(file_exists(public_path('formats/'.$format->file)));

        $pdf = app(AttendancePdfService::class)->generatePdf($event->load('dependency', 'area'), 'general');

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_el_comando_de_respaldo_copia_del_disco_a_la_bd(): void
    {
        $bytes = file_get_contents($this->realPdf());
        Storage::fake('formats');
        Storage::disk('formats')->put('acta.pdf', $bytes);

        $format = Format::create(['name' => 'Acta', 'slug' => 'acta', 'file' => 'acta.pdf']);
        $this->assertFalse($format->hasPdfInDb());

        $this->artisan('formats:pdf-a-bd')->assertSuccessful();

        $this->assertTrue($format->fresh()->hasPdfInDb());
        $this->assertSame($bytes, $format->fresh()->pdfContents());
    }
}
