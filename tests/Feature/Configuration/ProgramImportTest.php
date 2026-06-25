<?php

namespace Tests\Feature\Configuration;

use App\Models\AcademicProgram;
use App\Models\Campus;
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
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);
        $academicProgram = AcademicProgram::create(['name' => 'Ingenieria de sistemas']);

        Program::factory()->create([
            'name' => 'Ingenieria de sistemas - Maicao',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
            'academic_program_id' => $academicProgram->id,
        ]);

        $csv = implode("\n", [
            'Nombre,Tipo',
            'Ingenieria de sistemas - Maicao,Pregrado',
            ',Posgrado',
            'INGENIERIA DE SISTEMAS,Pregrado',
            'Matematicas - Maicao,Posgrado',
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
            'name' => 'Matematicas - Maicao',
            'program_type' => 'Posgrado',
            'campus_id' => $campus->id,
        ]);

        $this->assertDatabaseHas('academic_programs', [
            'name' => 'Matematicas',
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

    public function test_superadmin_omite_programas_sin_sufijo_de_sede_valido(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        $file = UploadedFile::fake()->createWithContent('programas.csv', implode("\n", [
            'Nombre,Tipo',
            'Ingenieria de sistemas - Maicao,Pregrado',
            'Derecho - Riohacha,Pregrado',
            'Licenciatura en etnoeducacion - Convenio Jorge Artel,Posgrado',
        ]));

        $this->actingAs($superadmin)
            ->post(route('programs.import'), ['excel_file' => $file])
            ->assertRedirect(route('programs.index'))
            ->assertSessionHas('import_result', ['created' => 2, 'skipped' => 1])
            ->assertSessionHas('programs_import_skipped', function (array $rows) {
                return str_contains($rows[0]['_motivo'], 'Agrega al final "- Sede"');
            });

        $this->assertDatabaseHas('programs', ['name' => 'Ingenieria de sistemas - Maicao', 'campus_id' => $maicao->id]);
        $this->assertDatabaseHas('programs', ['name' => 'Derecho - Riohacha', 'campus_id' => $riohacha->id]);
        $this->assertDatabaseMissing('programs', [
            'name' => 'Licenciatura en etnoeducacion - Convenio Jorge Artel',
        ]);
    }

    public function test_admin_recibe_reporte_cuando_un_programa_corresponde_a_otra_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $maicao->id]);
        $file = UploadedFile::fake()->createWithContent('programas.csv', implode("\n", [
            'Nombre,Tipo',
            'Derecho - Riohacha,Pregrado',
        ]));

        $this->actingAs($admin)
            ->post(route('programs.import'), ['excel_file' => $file])
            ->assertSessionHas('import_result', ['created' => 0, 'skipped' => 1])
            ->assertSessionHas('programs_import_skipped', function (array $rows) {
                return str_contains($rows[0]['_motivo'], 'no corresponde a tu sede');
            });
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
