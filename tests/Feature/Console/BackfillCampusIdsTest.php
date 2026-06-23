<?php

namespace Tests\Feature\Console;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackfillCampusIdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_backfill_no_asigna_sede_a_superadministradores(): void
    {
        foreach (['Albania', 'Maicao', 'Riohacha', 'Fonseca', 'Villanueva', 'Manaure', 'Monteria', 'Uribia'] as $name) {
            Campus::create(['name' => $name]);
        }

        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => null,
        ]);

        $this->assertSame(0, Artisan::call('campuses:backfill'));

        $this->assertNull($superadmin->fresh()->campus_id);
        $this->assertNotNull($admin->fresh()->campus_id);
    }

    public function test_el_backfill_reconoce_manaure_desde_el_nombre_historico(): void
    {
        foreach (['Albania', 'Maicao', 'Riohacha', 'Fonseca', 'Villanueva', 'Manaure', 'Monteria', 'Uribia'] as $name) {
            Campus::create(['name' => $name]);
        }

        $dependencyId = \Illuminate\Support\Facades\DB::table('dependencies')->insertGetId([
            'name' => 'Biblioteca - Manaure',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, Artisan::call('campuses:backfill'));

        $this->assertSame(
            Campus::where('name', 'Manaure')->value('id'),
            \Illuminate\Support\Facades\DB::table('dependencies')->where('id', $dependencyId)->value('campus_id'),
        );
    }

    public function test_el_dry_run_falla_sin_crear_sedes_faltantes(): void
    {
        $this->assertSame(1, Artisan::call('campuses:backfill', ['--dry-run' => true]));
        $this->assertSame(0, Campus::count());
    }
}
