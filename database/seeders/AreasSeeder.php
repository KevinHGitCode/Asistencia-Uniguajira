<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Dependency;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        $bienestar = Dependency::where('name', 'Bienestar Universitario')->first();
        $gestionAdmin = Dependency::where('name', 'Gestión Administrativa y Financiera')->first();
        $gestionDoc = Dependency::where('name', 'Gestión Documental')->first();
        $calidad = Dependency::where('name', 'Aseguramiento de la Calidad')->first();
        $direccionAcademica = Dependency::where('name', 'Dirección Académica')->first();

        // Bienestar Universitario
        Area::firstOrCreate([
            'name' => 'Salud',
            'dependency_id' => $bienestar->id
        ]);

        Area::firstOrCreate([
            'name' => 'Deporte',
            'dependency_id' => $bienestar->id
        ]);

        Area::firstOrCreate([
            'name' => 'Tutorías',
            'dependency_id' => $bienestar->id
        ]);

        // Gestión Administrativa y Financiera
        Area::firstOrCreate([
            'name' => 'Contabilidad',
            'dependency_id' => $gestionAdmin->id
        ]);

        Area::firstOrCreate([
            'name' => 'Tesorería',
            'dependency_id' => $gestionAdmin->id
        ]);

        // Gestión Documental
        Area::firstOrCreate([
            'name' => 'Archivo Central',
            'dependency_id' => $gestionDoc->id
        ]);

        // Aseguramiento de la Calidad
        Area::firstOrCreate([
            'name' => 'Evaluación Institucional',
            'dependency_id' => $calidad->id
        ]);

        Area::firstOrCreate([
            'name' => 'Acreditación',
            'dependency_id' => $calidad->id
        ]);

        // Dirección Académica
        Area::firstOrCreate([
            'name' => 'Planeación Académica',
            'dependency_id' => $direccionAcademica->id
        ]);

        Area::firstOrCreate([
            'name' => 'Registro y Control',
            'dependency_id' => $direccionAcademica->id
        ]);
    }
}
