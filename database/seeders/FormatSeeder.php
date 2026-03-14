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
        $general = Format::create(['name' => 'Formato General', 'slug' => 'general']);
        $bienestar = Format::create(['name' => 'Formato Bienestar', 'slug' => 'bienestar']);
        $proyeccion = Format::create(['name' => 'Formato Proyección Social', 'slug' => 'proyeccion_social']);
        $capExterna = Format::create(['name' => 'Formato Capacitación Externa', 'slug' => 'capacitacion_externa']);

        // Todas las dependencias tienen acceso al formato general
        $allDependencies = Dependency::all();
        foreach ($allDependencies as $dep) {
            $dep->formats()->attach($general->id);
        }

        // Bienestar (id 1) tiene su formato propio
        Dependency::find(1)?->formats()->attach($bienestar->id);

        // Proyección Social (ajusta el id) tiene 2 formatos propios
        $proyeccionDep = Dependency::find(6); // ajusta el id
        $proyeccionDep?->formats()->attach($proyeccion->id);
        $proyeccionDep?->formats()->attach($capExterna->id);
    }
}
