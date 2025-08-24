<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Program;
use App\Models\Affiliation;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/files/seed.xlsx');
        $sheets = Excel::toArray([], $path);
        $rows = $sheets[0];

        // Saltar cabecera
        array_shift($rows);

        // 1. Crear un hash de programas y subirlos a la base de datos
        $programHash = [];
        $programsToInsert = [];
        foreach ($rows as $row) {
            [$document, $firstName, $lastName, $roleName, $email, $programName, $programType, $affiliationType] = $row;
            $programType = match (strtolower($programType)) {
                'pregrado', 'undergraduate' => 'Pregrado',
                'posgrado', 'postgrado', 'postgraduate' => 'Posgrado',
                default => null,
            };
            $key = $programName . '|' . $programType;
            if (!isset($programHash[$key])) {
                $programHash[$key] = null; // placeholder
                $programsToInsert[$key] = [
                    'name' => $programName,
                    'program_type' => $programType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insertar programas únicos
        if (!empty($programsToInsert)) {
            Program::insert(array_values($programsToInsert));
        }
        // Obtener todos los programas de la base de datos y actualizar el hash con sus IDs
        foreach (Program::all() as $program) {
            $key = $program->name . '|' . $program->program_type;
            $programHash[$key] = $program->id;
        }

        // 2. Bulk insert de participantes en lotes de 500
        $participantsToInsert = [];
        foreach ($rows as $row) {
            [$document, $firstName, $lastName, $roleName, $email, $programName, $programType, $affiliationType] = $row;
            $programType = match (strtolower($programType)) {
                'pregrado', 'undergraduate' => 'Pregrado',
                'posgrado', 'postgrado', 'postgraduate' => 'Posgrado',
                default => null,
            };
            $key = $programName . '|' . $programType;
            $participantsToInsert[] = [
                'document' => $document,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'role' => $roleName,
                'affiliation' => ($affiliationType !== 0 && $affiliationType !== '0') ? $affiliationType : null,
                'program_id' => $programHash[$key] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($participantsToInsert) === 500) {
                Participant::insert($participantsToInsert);
                $participantsToInsert = [];
            }
        }
        if (!empty($participantsToInsert)) {
            Participant::insert($participantsToInsert);
        }
    }

}
