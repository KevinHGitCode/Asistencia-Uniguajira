<?php

namespace Tests\Feature\Administration;

use App\Livewire\Administration\DependencyTable;
use App\Models\AcademicProgram;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusScopedConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crea_dependencia_en_su_sede(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $this->actingAs($admin)
            ->post(route('dependencies.store'), ['name' => 'Bienestar Maicao'])
            ->assertRedirect(route('dependencies.index'));

        $this->assertDatabaseHas('dependencies', [
            'name' => 'Bienestar maicao - Maicao',
            'campus_id' => $campus->id,
        ]);
    }

    public function test_superadmin_puede_crear_la_misma_dependencia_en_sedes_distintas(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);

        $this->actingAs($superadmin)
            ->post(route('dependencies.store'), [
                'name' => 'Aseguramiento de la calidad',
                'campus_id' => $maicao->id,
            ])
            ->assertRedirect(route('dependencies.index'));

        $this->actingAs($superadmin)
            ->post(route('dependencies.store'), [
                'name' => 'Aseguramiento de la calidad',
                'campus_id' => $riohacha->id,
            ])
            ->assertRedirect(route('dependencies.index'));

        $this->assertDatabaseHas('dependencies', [
            'name' => 'Aseguramiento de la calidad - Maicao',
            'campus_id' => $maicao->id,
        ]);
        $this->assertDatabaseHas('dependencies', [
            'name' => 'Aseguramiento de la calidad - Riohacha',
            'campus_id' => $riohacha->id,
        ]);
    }

    public function test_superadmin_reemplaza_un_sufijo_de_sede_escrito_por_la_sede_seleccionada(): void
    {
        $fonseca = Campus::create(['name' => 'Fonseca']);
        $maicao = Campus::create(['name' => 'Maicao']);
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);

        $this->actingAs($superadmin)
            ->post(route('dependencies.store'), [
                'name' => 'Prueba - Maicao',
                'campus_id' => $fonseca->id,
            ])
            ->assertRedirect(route('dependencies.index'));

        $this->assertDatabaseHas('dependencies', [
            'name' => 'Prueba - Fonseca',
            'campus_id' => $fonseca->id,
        ]);
    }

    public function test_admin_no_puede_editar_dependencia_de_otra_sede(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $maicao->id]);
        $dependency = Dependency::factory()->create([
            'name' => 'Biblioteca Riohacha',
            'campus_id' => $riohacha->id,
        ]);

        $this->actingAs($admin)
            ->post(route('dependencies.update', $dependency), ['name' => 'Biblioteca'])
            ->assertForbidden();
    }

    public function test_admin_crea_programa_en_su_sede_con_programa_academico(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        $this->actingAs($admin)
            ->post(route('programs.store'), [
                'name' => 'Administracion de empresas',
                'program_type' => 'Pregrado',
            ])
            ->assertRedirect(route('programs.index'));

        $academicProgram = AcademicProgram::where('name', 'Administracion de empresas')->firstOrFail();

        $this->assertDatabaseHas('programs', [
            'name' => 'Administracion de empresas - Maicao',
            'program_type' => 'Pregrado',
            'campus_id' => $campus->id,
            'academic_program_id' => $academicProgram->id,
        ]);
    }

    public function test_superadmin_sin_sede_debe_seleccionar_sede_para_crear_programa(): void
    {
        Campus::create(['name' => 'Maicao']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);

        $this->actingAs($superadmin)
            ->post(route('programs.store'), ['name' => 'Derecho'])
            ->assertSessionHasErrors('campus_id');

        $this->assertDatabaseMissing('programs', [
            'name' => 'Derecho - Maicao',
        ]);
    }

    public function test_no_duplica_programa_academico_en_la_misma_sede(): void
    {
        $campus = Campus::create(['name' => 'Maicao']);
        $academicProgram = AcademicProgram::create(['name' => 'Contaduria publica']);
        $admin = User::factory()->create(['role' => 'admin', 'campus_id' => $campus->id]);

        Program::factory()->create([
            'name' => 'Contaduria publica - Maicao',
            'campus_id' => $campus->id,
            'academic_program_id' => $academicProgram->id,
        ]);

        $this->actingAs($admin)
            ->post(route('programs.store'), ['academic_program_id' => $academicProgram->id])
            ->assertSessionHasErrors('academic_program_id');
    }

    public function test_superadmin_puede_cambiar_la_sede_de_una_dependencia(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        $dependency = Dependency::factory()->create(['campus_id' => $maicao->id]);

        $this->actingAs($superadmin)
            ->post(route('dependencies.update', $dependency), [
                'name' => $dependency->name,
                'campus_id' => $riohacha->id,
            ])
            ->assertRedirect(route('dependencies.index'));

        $this->assertDatabaseHas('dependencies', [
            'id' => $dependency->id,
            'campus_id' => $riohacha->id,
        ]);
    }

    public function test_superadmin_filtra_la_tabla_de_dependencias_sin_recargar_la_pagina(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $superadmin = User::factory()->create(['role' => 'superadmin', 'campus_id' => null]);
        $dependencyMaicao = Dependency::factory()->create(['name' => 'Bienestar Maicao', 'campus_id' => $maicao->id]);
        $dependencyRiohacha = Dependency::factory()->create(['name' => 'Bienestar Riohacha', 'campus_id' => $riohacha->id]);

        \Livewire\Livewire::actingAs($superadmin)
            ->test(DependencyTable::class)
            ->set('campusId', (string) $maicao->id)
            ->assertSee($dependencyMaicao->name)
            ->assertDontSee($dependencyRiohacha->name);
    }
}
