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

            // Separar el nombre del programa y la sede
            [$programName, $campus] = array_map('trim', explode(' - ', $programName) + [null, null]);

            // Convertir a Title Case
            $programName = ucwords(strtolower($programName));
            $campus = $campus ? ucwords(strtolower($campus)) : null;

            $programType = match (strtolower($programType)) {
                'pregrado' => 'Pregrado',
                'posgrado', 'postgrado' => 'Posgrado',
                default => null,
            };

            $programsToInsert[$programName . '|' . $campus . '|' . $programType] = [
                'name' => $programName,
                'campus' => $campus,
                'program_type' => $programType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($programsToInsert)) {
            Program::upsert(
                array_values($programsToInsert),
                ['name', 'campus'], // Clave única incluye campus
                ['program_type', 'updated_at']
            );
        }
    }
}
