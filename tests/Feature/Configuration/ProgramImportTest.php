<?php

namespace Tests\Feature\Configuration;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProgramImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_guarda_programas_y_registra_filas_omitidas_para_descarga(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Program::factory()->create(['name' => 'Ingenieria de sistemas', 'program_type' => 'Pregrado']);

        $csv = implode("\n", [
            'Nombre,Tipo',
            'Ingenieria de sistemas,Pregrado',
            ',Posgrado',
            'INGENIERIA DE SISTEMAS,Pregrado',
            'Matematicas,Posgrado',
        ]);

        $file = UploadedFile::fake()->createWithContent('programas.csv', $csv);

        $this->actingAs($admin)
            ->post(route('programs.import'), ['excel_file' => $file])
            ->assertRedirect(route('programs.index'))
            ->assertSessionHas('import_result', function (array $result) {
                return ($result['created'] ?? null) === 1
                    && ($result['skipped'] ?? null) === 3;
            })
            ->assertSessionHas('programs_import_skipped', function (array $rows) {
                return count($rows) === 3
                    && isset($rows[0]['_motivo']);
            });

        $this->assertDatabaseHas('programs', [
            'name' => 'Matematicas',
            'program_type' => 'Posgrado',
        ]);
    }

    public function test_descarga_excel_de_omitidos_si_existe_en_sesion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withSession([
                'programs_import_skipped' => [
                    [
                        'Nombre' => 'Ingenieria de sistemas',
                        'Tipo' => 'Pregrado',
                        '_motivo' => 'Programa duplicado o ya existente: "Ingenieria de sistemas"',
                    ],
                ],
            ])
            ->get(route('programs.download-skipped'));

        $response->assertOk();
        $contentDisposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment;', $contentDisposition);
        $this->assertStringContainsString('programas_omitidos_', $contentDisposition);
        $this->assertStringContainsString('.xlsx', $contentDisposition);
    }

    public function test_redirige_con_error_si_no_hay_omitidos_para_descargar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('programs.download-skipped'))
            ->assertRedirect(route('programs.index'))
            ->assertSessionHas('error');
    }
}
