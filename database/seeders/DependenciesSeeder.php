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
        Dependency::create(['name' => 'Bienestar Universitario']);
        Dependency::create(['name' => 'Gestión Administrativa y Financiera']);
        Dependency::create(['name' => 'Gestión Documental']);
        Dependency::create(['name' => 'Aseguramiento de la Calidad']);
        Dependency::create(['name' => 'Dirección Académica']);
    }
}
