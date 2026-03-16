<?php

namespace Database\Seeders;

use App\Models\Dependency;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DependenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dependency::firstOrCreate(['name' => 'Bienestar Universitario']);
        Dependency::firstOrCreate(['name' => 'Gestión Administrativa y Financiera']);
        Dependency::firstOrCreate(['name' => 'Gestión Documental']);
        Dependency::firstOrCreate(['name' => 'Aseguramiento de la Calidad']);
        Dependency::firstOrCreate(['name' => 'Dirección Académica']);
        Dependency::firstOrCreate(['name' => 'Proyección Social']);
    }
}
