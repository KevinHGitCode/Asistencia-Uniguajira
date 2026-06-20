<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = [
            'Maicao',
            'Riohacha',
            'Fonseca',
            'Villanueva',
        ];

        foreach ($campuses as $name) {
            Campus::firstOrCreate(['name' => $name]);
        }
    }
}
