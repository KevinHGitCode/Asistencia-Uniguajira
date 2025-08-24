<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Participant;
use App\Models\Program;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/files/seed.xlsx');
        $sheets = Excel::toArray([], $path);
        $rows = $sheets[0];

        // Saltar cabecera
        array_shift($rows);

        // Construir hash de programas existentes
        $programHash = [];
        foreach (Program::all() as $program) {
            $key = $program->name . '|' . $program->program_type;
            $programHash[$key] = $program->id;
        }

        // Insertar participantes en lotes
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
                'document'    => $document,
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'email'       => $email,
                'role'        => $roleName,
                'affiliation' => ($affiliationType !== 0 && $affiliationType !== '0') ? $affiliationType : null,
                'program_id'  => $programHash[$key] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
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
