<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Participant;
use App\Models\Program;

class ParticipantSeeder extends Seeder
{
    // Constantes configurables
    private const BATCH_SIZE = 500; // TamaÃ±o del lote para inserciones masivas

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
            $key = strtolower($program->name) . '|' . strtolower($program->campus);
            $programHash[$key] = $program->id;
        }

        // Insertar participantes en lotes
        $participantsToInsert = [];
        foreach ($rows as $row) {
            [$document, $firstName, $lastName, $roleName, $email, $programName, $programType, $affiliationType] = $row;

            // Convertir nombres a Title Case
            $firstName = ucwords(strtolower($firstName));
            $lastName = ucwords(strtolower($lastName));

            // Separar el nombre del programa y la sede
            [$programName, $campus] = array_map('trim', explode(' - ', $programName) + [null, null]);

            // Convertir a minÃºsculas para la comparaciÃ³n
            $programKey = strtolower($programName) . '|' . strtolower($campus);

            $participantsToInsert[] = [
                'document'         => $document,
                'student_code'     => null, // El Excel no contiene código estudiantil
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'email'            => $email ?: null,
                'role'             => $roleName,
                'affiliation'      => ($affiliationType !== 0 && $affiliationType !== '0') ? $affiliationType : null,
                // sexo y grupo_priorizado aleatorios para datos de prueba
                'sexo'             => ['Masculino', 'Femenino', 'No binario'][array_rand(['Masculino', 'Femenino', 'No binario'])],
                'grupo_priorizado' => ['Ninguno', 'Comunidades indígenas', 'Comunidades afrodescendientes', 'Población con discapacidad', 'Víctimas del conflicto armado', 'Jóvenes rurales', 'LGBTIQ+'][array_rand(['Ninguno', 'Comunidades indígenas', 'Comunidades afrodescendientes', 'Población con discapacidad', 'Víctimas del conflicto armado', 'Jóvenes rurales', 'LGBTIQ+'])],
                'program_id'       => $programHash[$programKey] ?? null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($participantsToInsert) === self::BATCH_SIZE) {
                Participant::insert($participantsToInsert);
                $participantsToInsert = [];
            }
        }

        if (!empty($participantsToInsert)) {
            Participant::insert($participantsToInsert);
        }
    }
}
