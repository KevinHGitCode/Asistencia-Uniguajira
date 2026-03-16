<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/files/BASE DE DATOS MAICAO.xlsx');
        if (! file_exists($path)) {
            return;
        }

        $sheets = Excel::toArray([], $path);
        $rows = $sheets[0] ?? [];
        if (empty($rows)) {
            return;
        }

        $headerRow = array_values((array) $rows[0]);
        $headers = array_map(fn ($h) => trim((string) ($h ?? '')), $headerRow);

        $hasHeader = in_array('Programa o Dependencia', $headers, true)
            || in_array('Documento', $headers, true);

        $colIndex = [];
        if ($hasHeader) {
            foreach ($headers as $pos => $name) {
                if ($name !== '') {
                    $colIndex[$name] = $pos;
                }
            }
            array_shift($rows);
        } else {
            $colIndex = [
                'Programa o Dependencia' => 5,
                'Tipo_progama' => 6,
            ];
        }

        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        $programsToInsert = [];
        $now = now();
        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $programNameRaw = $get($rawValues, 'Programa o Dependencia');
            if ($programNameRaw === null || trim((string) $programNameRaw) === '') {
                $maybeProgram = $get($rawValues, 'Correo');
                if ($maybeProgram !== null && trim((string) $maybeProgram) !== ''
                    && ! str_contains((string) $maybeProgram, '@')) {
                    $programNameRaw = $maybeProgram;
                }
            }
            $programTypeRaw = $get($rawValues, 'Tipo_progama');
            if ($programNameRaw === null || trim((string) $programNameRaw) === '') {
                continue;
            }

            $parts = array_map('trim', explode(' - ', (string) $programNameRaw, 2));
            $programName = $parts[0] ?? '';
            $campus = $parts[1] ?? null;
            if ($programName === '') {
                continue;
            }

            $programName = ucwords(strtolower($programName));
            $campus = $campus ? ucwords(strtolower($campus)) : null;

            $programType = null;
            if ($programTypeRaw !== null && trim((string) $programTypeRaw) !== '') {
                $programType = match (strtolower(trim((string) $programTypeRaw))) {
                    'pregrado' => 'Pregrado',
                    'posgrado', 'postgrado' => 'Posgrado',
                    default => null,
                };
            }

            $key = strtolower($programName) . '|' . strtolower($campus ?? '');
            if (! isset($programsToInsert[$key])) {
                $programsToInsert[$key] = [
                    'name' => $programName,
                    'campus' => $campus,
                    'program_type' => $programType,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } elseif ($programsToInsert[$key]['program_type'] === null && $programType !== null) {
                $programsToInsert[$key]['program_type'] = $programType;
            }
        }

        if (! empty($programsToInsert)) {
            Program::upsert(
                array_values($programsToInsert),
                ['name', 'campus'],
                ['program_type', 'updated_at']
            );
        }
    }
}
