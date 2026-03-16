<?php

namespace Database\Seeders;

use App\Models\ParticipantType;
use Illuminate\Database\Seeder;

class ParticipantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Estudiante',
            'Docente',
            'Administrativo',
            'Graduado',
            'Comunidad Externa',
            'Bolsa',
        ];

        foreach ($defaults as $name) {
            ParticipantType::firstOrCreate(['name' => $name]);
        }
    }
}
