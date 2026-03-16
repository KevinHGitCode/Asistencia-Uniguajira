<?php

namespace Database\Seeders;

use App\Models\Dependency;
use App\Models\Format;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $general = Format::firstOrCreate([
            'name' => 'Formato General',
            'slug' => 'general',
            'file' => 'LISTADO_DE_ASISTENCIA_GENERAL_REVISION_9.pdf',
        ]);

        $bienestar = Format::firstOrCreate([
            'name' => 'Formato Bienestar',
            'slug' => 'bienestar',
            'file' => 'LISTADO_DE_ASISTENCIA_BIENESTAR_REVISION_6.pdf',
        ]);

        $proyeccion = Format::firstOrCreate([
            'name' => 'Formato Proyección Social',
            'slug' => 'proyeccion_social',
            'file' => 'LISTADO_DE_ASISTENCIA_PROYECCION_SOCIAL_REVISION_5.pdf',
        ]);

        $capExterna = Format::firstOrCreate([
            'name' => 'Formato Capacitación Externa',
            'slug' => 'capacitacion_externa',
            'file' => 'LISTADO_DE_ASISTENCIA_CAPACITACION_EXTERNA_PROYECCION_SOCIAL_REVISION_7.pdf',
        ]);

        $allDependencies = Dependency::all();
        foreach ($allDependencies as $dep) {
            $dep->formats()->attach($general->id);
        }

        Dependency::find(1)?->formats()->attach($bienestar->id);

        $proyeccionDep = Dependency::find(6);
        $proyeccionDep?->formats()->attach($proyeccion->id);
        $proyeccionDep?->formats()->attach($capExterna->id);
    }
}
