<?php

namespace Tests\Feature\Configuration;

use App\Models\Format;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ADR-0015 — El mapeo vive en la BD (única fuente de verdad); ya no se escribe
 * el espejo en config/ en runtime, y cambiar el PDF marca el mapeo como pendiente.
 */
class FormatMappingSourceOfTruthTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
    }

    private function validMapping(): array
    {
        return [
            'file' => 'x.pdf',
            'startY' => 60,
            'rowHeight' => 8,
            'maxRows' => 16,
            'columns' => ['name' => ['x' => 30, 'w' => 60, 'align' => 'L']],
            'header' => [],
        ];
    }

    public function test_guardar_mapeo_no_escribe_el_espejo_en_config(): void
    {
        $configPath = config_path('attendance_formats.php');
        $antes = file_exists($configPath) ? file_get_contents($configPath) : null;

        $format = Format::create(['name' => 'Acta', 'slug' => 'acta', 'mapping_outdated' => true]);

        $this->actingAs($this->superadmin())
            ->postJson(route('formats.save-mapping', $format), ['mapping' => $this->validMapping()])
            ->assertOk()
            ->assertJson(['success' => true]);

        // La BD quedó como fuente de verdad y al día.
        $format->refresh();
        $this->assertSame(60, $format->mapping['startY']);
        $this->assertFalse($format->mapping_outdated);

        // El archivo de config NO se tocó en runtime (ADR-0015).
        $despues = file_exists($configPath) ? file_get_contents($configPath) : null;
        $this->assertSame($antes, $despues, 'saveMapping no debe reescribir config/attendance_formats.php');
    }

    public function test_cambiar_el_pdf_marca_el_mapeo_como_pendiente(): void
    {
        $pdfs = glob(public_path('formats').DIRECTORY_SEPARATOR.'*.pdf');
        if (empty($pdfs)) {
            $this->markTestSkipped('No hay un PDF real en public/formats para usar como fixture.');
        }

        Storage::fake('formats');

        $format = Format::create([
            'name' => 'Acta',
            'slug' => 'acta',
            'file' => 'acta_old.pdf',
            'mapping' => $this->validMapping(),
            'mapping_outdated' => false,
        ]);

        $file = new UploadedFile($pdfs[0], 'nuevo.pdf', 'application/pdf', null, true);

        $this->actingAs($this->superadmin())
            ->post(route('formats.update', $format), [
                'name' => 'Acta',
                'slug' => 'acta',
                'pdf_file' => $file,
            ])
            ->assertRedirect(route('formats.index'));

        $this->assertTrue($format->fresh()->mapping_outdated, 'Cambiar el PDF debe marcar el mapeo como pendiente.');
    }

    public function test_cambiar_el_pdf_sin_mapeo_previo_no_marca_pendiente(): void
    {
        $pdfs = glob(public_path('formats').DIRECTORY_SEPARATOR.'*.pdf');
        if (empty($pdfs)) {
            $this->markTestSkipped('No hay un PDF real en public/formats para usar como fixture.');
        }

        Storage::fake('formats');

        $format = Format::create(['name' => 'Nuevo', 'slug' => 'nuevo', 'mapping' => null]);
        $file = new UploadedFile($pdfs[0], 'primero.pdf', 'application/pdf', null, true);

        $this->actingAs($this->superadmin())
            ->post(route('formats.update', $format), ['name' => 'Nuevo', 'slug' => 'nuevo', 'pdf_file' => $file])
            ->assertRedirect();

        $this->assertFalse($format->fresh()->mapping_outdated);
    }

    public function test_eliminar_formato_funciona_sin_espejo_de_config(): void
    {
        $format = Format::create(['name' => 'Borrar', 'slug' => 'borrar', 'mapping' => $this->validMapping()]);

        $this->actingAs($this->superadmin())
            ->post(route('formats.destroy', $format))
            ->assertRedirect(route('formats.index'));

        $this->assertDatabaseMissing('formats', ['id' => $format->id]);
    }
}
