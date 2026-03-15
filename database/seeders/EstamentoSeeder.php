<?php

namespace Database\Seeders;

use App\Models\Estamento;
use Illuminate\Database\Seeder;

class EstamentoSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Estudiante',
            'Docente',
            'Administrativo',
            'Graduado',
            'Comunidad Externa',
        ];

        foreach ($defaults as $name) {
            Estamento::firstOrCreate(['name' => $name]);
        }
    }
}
