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

        foreach ($rows as $row) {
            [$document, $firstName, $lastName, $roleName, $email, $programName, $programType, $affiliationType] = $row;

            // Roles (ej: "Estudiante", "Docente")
            $role = Role::firstOrCreate(['role_type' => $roleName]);

            //
            $programType = match (strtolower($programType)) {
                'pregrado', 'undergraduate' => 'Pregrado',
                'posgrado', 'postgrado', 'postgraduate' => 'Posgrado',
                default => null,
            };

            // Programas (ej: "INGENIERIA DE SISTEMAS - RIOHACHA", "Pregrado")
            $program = Program::firstOrCreate(
                ['name' => $programName],
                ['program_type' => $programType]
            );

            // Vinculación (0 = null, otro valor = texto real)
            $affiliation = null;
            if ($affiliationType !== 0 && $affiliationType !== '0') {
                $affiliation = Affiliation::firstOrCreate(['affiliation_type' => $affiliationType]);
            }

            // Crear o actualizar Participant
            Participant::updateOrCreate(
                ['document' => $document],
                [
                    'first_name'     => $firstName,
                    'last_name'      => $lastName,
                    'email'          => $email,
                    'role_id'        => $role->id,
                    'program_id'     => $program->id,
                    'affiliation_id' => $affiliation?->id, // null si es 0
                ]
            );
        }

        // // Ejemplo básico de lectura de Excel con maatwebsite/excel
        // // Ruta hacia tu archivo
        // $path = database_path('seeders/files/attendances.xlsx');

        // // Leer todo en arrays (cada hoja es un array)
        // $sheets = Excel::toArray([], $path);

        // // Tomar la primera hoja
        // $rows = $sheets[0];

        // // Mostrar filas en consola
        // foreach ($rows as $row) {
        //     dump($row);
        // }
    }
}
