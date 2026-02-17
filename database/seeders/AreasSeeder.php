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
        Area::create([
            'name' => 'Salud',
            'dependency_id' => $bienestar->id
        ]);

        Area::create([
            'name' => 'Deporte',
            'dependency_id' => $bienestar->id
        ]);

        Area::create([
            'name' => 'Tutorías',
            'dependency_id' => $bienestar->id
        ]);

        // Gestión Administrativa y Financiera
        Area::create([
            'name' => 'Contabilidad',
            'dependency_id' => $gestionAdmin->id
        ]);

        Area::create([
            'name' => 'Tesorería',
            'dependency_id' => $gestionAdmin->id
        ]);

        // Gestión Documental
        Area::create([
            'name' => 'Archivo Central',
            'dependency_id' => $gestionDoc->id
        ]);

        // Aseguramiento de la Calidad
        Area::create([
            'name' => 'Evaluación Institucional',
            'dependency_id' => $calidad->id
        ]);

        Area::create([
            'name' => 'Acreditación',
            'dependency_id' => $calidad->id
        ]);

        // Dirección Académica
        Area::create([
            'name' => 'Planeación Académica',
            'dependency_id' => $direccionAcademica->id
        ]);

        Area::create([
            'name' => 'Registro y Control',
            'dependency_id' => $direccionAcademica->id
        ]);
    }
}
