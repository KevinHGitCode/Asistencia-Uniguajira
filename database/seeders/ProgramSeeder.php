<?php

namespace Database\Seeders;

use App\Http\Controllers\Configuration\ProgramController;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/files/seed.xlsx');
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

        // Cache de programas existentes usando la misma clave de comparación del controlador
        $existingSet = array_flip(
            Program::all(['name'])->map(fn ($p) => ProgramController::comparisonKey($p->name))->toArray()
        );

        $programsToInsert = [];
        $now = now()->toDateTimeString();

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
            $rawName = $parts[0] ?? '';
            $rawCampus = $parts[1] ?? null;
            if ($rawName === '') {
                continue;
            }

            // Normalización UTF-8 idéntica al controlador
            $programName = ProgramController::normalizeName($rawName);
            $campus = $rawCampus ? ProgramController::normalizeName($rawCampus) : null;

            $programType = null;
            if ($programTypeRaw !== null && trim((string) $programTypeRaw) !== '') {
                $programType = match (mb_strtolower(trim((string) $programTypeRaw), 'UTF-8')) {
                    'pregrado' => 'Pregrado',
                    'posgrado', 'postgrado' => 'Posgrado',
                    default => null,
                };
            }

            // Clave de comparación sin acentos (igual que importExcel del controlador)
            $nameKey = ProgramController::comparisonKey($programName);
            $campusKey = $campus ? ProgramController::comparisonKey($campus) : '';
            $compositeKey = $nameKey . '|' . $campusKey;

            if (isset($existingSet[$nameKey])) {
                // Ya existe en BD o ya se procesó en este lote, saltar
                continue;
            }

            // Marcar como existente para que filas posteriores con tildes/espacios
            // diferentes no se dupliquen (misma lógica que importExcel)
            $existingSet[$nameKey] = true;

            if (! isset($programsToInsert[$compositeKey])) {
                $programsToInsert[$compositeKey] = [
                    'name' => $programName,
                    'campus' => $campus,
                    'program_type' => $programType,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } elseif ($programsToInsert[$compositeKey]['program_type'] === null && $programType !== null) {
                $programsToInsert[$compositeKey]['program_type'] = $programType;
            }
        }

        if (! empty($programsToInsert)) {
            foreach (array_chunk(array_values($programsToInsert), 500) as $chunk) {
                DB::table('programs')->insert($chunk);
            }
        }
    }
}