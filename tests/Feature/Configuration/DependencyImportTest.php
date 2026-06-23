<?php

namespace Tests\Feature\Configuration;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DependencyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_omite_dependencias_sin_sufijo_de_sede_valido(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $manaure = Campus::create(['name' => 'Manaure']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        $file = UploadedFile::fake()->createWithContent('dependencias.csv', implode("\n", [
            'Nombre',
            '"Biblioteca - Maicao"',
            '"Bienestar - Riohacha"',
            'Oficina sin sede',
            '"Biblioteca - Externo"',
            '"Dirección de bienes - Manaure"',
        ]));

        $this->actingAs($superadmin)
            ->post(route('dependencies.import'), ['excel_file' => $file])
            ->assertRedirect(route('dependencies.index'))
            ->assertSessionHas('import_result', ['created' => 3, 'skipped' => 2])
            ->assertSessionHas('dependencies_import_skipped', function (array $rows) {
                return count($rows) === 2
                    && collect($rows)->every(fn (array $row) => str_contains($row['_motivo'], 'Agrega al final "- Sede"'));
            });

        $this->assertDatabaseHas('dependencies', ['name' => 'Biblioteca - Maicao', 'campus_id' => $maicao->id]);
        $this->assertDatabaseHas('dependencies', ['name' => 'Bienestar - Riohacha', 'campus_id' => $riohacha->id]);
        $this->assertDatabaseHas('dependencies', ['campus_id' => $manaure->id]);
        $this->assertDatabaseMissing('dependencies', ['name' => 'Oficina sin sede']);
        $this->assertDatabaseMissing('dependencies', ['name' => 'Biblioteca - externo']);
        $this->assertDatabaseMissing('campuses', ['name' => 'Externo']);
    }

    public function test_admin_recibe_reporte_para_dependencias_de_otra_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $maicao->id]);
        $file = UploadedFile::fake()->createWithContent('dependencias.csv', implode("\n", [
            'Nombre',
            '"Biblioteca - Maicao"',
            '"Bienestar - Riohacha"',
        ]));

        $this->actingAs($admin)
            ->post(route('dependencies.import'), ['excel_file' => $file])
            ->assertRedirect(route('dependencies.index'))
            ->assertSessionHas('import_result', ['created' => 1, 'skipped' => 1])
            ->assertSessionHas('dependencies_import_skipped', function (array $rows) {
                return count($rows) === 1
                    && str_contains($rows[0]['_motivo'], 'no corresponde a tu sede');
            });

        $this->assertDatabaseMissing('dependencies', ['name' => 'Bienestar']);
        $this->assertDatabaseHas('dependencies', ['name' => 'Biblioteca - Maicao', 'campus_id' => $maicao->id]);
    }

    public function test_superadmin_importa_el_mismo_nombre_en_sedes_distintas(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $villanueva = Campus::create(['name' => 'Villanueva']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        $file = UploadedFile::fake()->createWithContent('dependencias.csv', implode("\n", [
            'Nombre',
            '"Aseguramiento de la calidad - Maicao"',
            '"Aseguramiento de la calidad - Villanueva"',
        ]));

        $this->actingAs($superadmin)
            ->post(route('dependencies.import'), ['excel_file' => $file])
            ->assertRedirect(route('dependencies.index'))
            ->assertSessionHas('import_result', ['created' => 2, 'skipped' => 0]);

        $this->assertDatabaseHas('dependencies', ['name' => 'Aseguramiento de la calidad - Maicao', 'campus_id' => $maicao->id]);
        $this->assertDatabaseHas('dependencies', ['name' => 'Aseguramiento de la calidad - Villanueva', 'campus_id' => $villanueva->id]);
    }

    public function test_admin_omite_un_sufijo_que_no_corresponde_a_una_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $maicao->id]);
        $file = UploadedFile::fake()->createWithContent('dependencias.csv', implode("\n", [
            'Nombre',
            '"Biblioteca - Externo"',
        ]));

        $this->actingAs($admin)
            ->post(route('dependencies.import'), ['excel_file' => $file])
            ->assertRedirect(route('dependencies.index'))
            ->assertSessionHas('import_result', ['created' => 0, 'skipped' => 1])
            ->assertSessionHas('dependencies_import_skipped', function (array $rows) {
                return str_contains($rows[0]['_motivo'], 'Agrega al final "- Sede"');
            });

        $this->assertDatabaseMissing('dependencies', ['name' => 'Biblioteca - externo']);
        $this->assertDatabaseMissing('campuses', ['name' => 'Externo']);
    }

    public function test_descarga_el_reporte_de_dependencias_omitidas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withSession(['dependencies_import_skipped' => [['Nombre' => 'Bienestar - Riohacha', '_motivo' => 'Otra sede']]])
            ->get(route('dependencies.download-skipped'));

        $response->assertOk();
        $this->assertStringContainsString('dependencias_omitidas_', (string) $response->headers->get('content-disposition'));
    }
}
