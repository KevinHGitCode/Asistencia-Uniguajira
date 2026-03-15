<?php

namespace Database\Seeders;

use App\Models\Affiliation;
use Illuminate\Database\Seeder;

class AffiliationSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Catedratico',
            'Ocasional',
            'Planta',
        ];

        foreach ($defaults as $name) {
            Affiliation::firstOrCreate(['name' => $name]);
        }
    }
}
