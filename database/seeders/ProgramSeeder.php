<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/files/seed.xlsx');
        $sheets = Excel::toArray([], $path);
        $rows = $sheets[0];

        // Saltar cabecera
        array_shift($rows);

        $programsToInsert = [];
        foreach ($rows as $row) {
            [, , , , , $programName, $programType] = $row;

            $programType = match (strtolower($programType)) {
                'pregrado' => 'Pregrado',
                'posgrado', 'postgrado' => 'Posgrado',
                default => null,
            };

            $programsToInsert[$programName . '|' . $programType] = [
                'name' => $programName,
                'program_type' => $programType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($programsToInsert)) {
            Program::upsert(
                array_values($programsToInsert),
                ['name'],
                ['program_type', 'updated_at']
            );
        }
    }
}
