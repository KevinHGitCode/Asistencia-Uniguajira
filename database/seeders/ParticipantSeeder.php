<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Affiliation;
use App\Models\Program;

class ParticipantSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $path   = database_path('seeders/files/seed.xlsx');
        $sheets = Excel::toArray([], $path);
        $rows   = $sheets[0];
        array_shift($rows); // saltar cabecera

        // ── Cachés de lookup ──────────────────────────────────────────────
        $programHash = [];
        foreach (Program::all(['id', 'name', 'campus']) as $program) {
            $key = strtolower($program->name) . '|' . strtolower($program->campus ?? '');
            $programHash[$key] = $program->id;
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $aff) {
            $affiliationHash[strtolower($aff->name)] = $aff->id;
        }

        // ── Primera pasada: agrupar por documento ─────────────────────────
        // $participantMap[document] = [participant fields]
        // $programMap[document]     = [program_ids]
        $participantMap = [];
        $programMap     = [];

        $validRoles = ['Estudiante', 'Docente', 'Administrativo', 'Graduado', 'Comunidad Externa'];

        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            [$document, $firstName, $lastName, $roleName, $email, $programName, , $affiliationType]
                = array_pad($rawValues, 8, null);

            $document  = trim((string) ($document ?? ''));
            $firstName = ucwords(strtolower(trim((string) ($firstName ?? ''))));
            $lastName  = ucwords(strtolower(trim((string) ($lastName ?? ''))));
            $roleName  = trim((string) ($roleName ?? ''));
            $email     = $email ? strtolower(trim((string) $email)) : null;

            if (empty($document)) continue;

            if (! in_array($roleName, $validRoles, true)) {
                $roleName = 'Comunidad Externa';
            }

            // Resolver program_id
            $programId = null;
            if (! empty($programName)) {
                $parts      = explode(' - ', (string) $programName, 2);
                $programKey = strtolower(trim($parts[0])) . '|' . strtolower(trim($parts[1] ?? ''));
                $programId  = $programHash[$programKey] ?? null;
            }

            // Resolver affiliation_id
            $affiliationId = null;
            if (! empty($affiliationType) && $affiliationType !== '0' && $affiliationType !== 0) {
                $affKey = strtolower(trim((string) $affiliationType));
                if (! isset($affiliationHash[$affKey])) {
                    $aff = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            if (isset($participantMap[$document])) {
                // Mismo participante, distinta carrera → acumular program_id
                if ($programId && ! in_array($programId, $programMap[$document] ?? [])) {
                    $programMap[$document][] = $programId;
                }
            } else {
                $participantMap[$document] = [
                    'document'         => $document,
                    'student_code'     => null,
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'email'            => $email ?: null,
                    'role'             => $roleName,
                    'affiliation_id'   => $affiliationId,
                    'sexo'             => ['Masculino', 'Femenino', 'No binario'][array_rand(['Masculino', 'Femenino', 'No binario'])],
                    'grupo_priorizado' => ['Ninguno', 'Comunidades indígenas', 'Comunidades afrodescendientes', 'Población con discapacidad', 'Víctimas del conflicto armado', 'Jóvenes rurales', 'LGBTIQ+'][array_rand(['Ninguno', 'Comunidades indígenas', 'Comunidades afrodescendientes', 'Población con discapacidad', 'Víctimas del conflicto armado', 'Jóvenes rurales', 'LGBTIQ+'])],
                    'created_at'       => now()->toDateTimeString(),
                    'updated_at'       => now()->toDateTimeString(),
                ];
                $programMap[$document] = $programId ? [$programId] : [];
            }
        }

        // ── Segunda pasada: insertar participantes en lotes ───────────────
        $now    = now()->toDateTimeString();
        $batch  = [];
        $documents = [];

        foreach ($participantMap as $doc => $data) {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $batch[]    = $data;
            $documents[] = $doc;

            if (count($batch) === self::BATCH_SIZE) {
                DB::table('participants')->insert($batch);
                $batch = [];
            }
        }
        if (! empty($batch)) {
            DB::table('participants')->insert($batch);
        }

        // ── Tercera pasada: insertar pivot participant_program ─────────────
        // Recuperar IDs recién insertados
        $docToId = DB::table('participants')
            ->whereIn('document', $documents)
            ->pluck('id', 'document')
            ->toArray();

        $pivotBatch = [];
        foreach ($documents as $doc) {
            $participantId = $docToId[$doc] ?? null;
            if (! $participantId) continue;

            foreach (($programMap[$doc] ?? []) as $programId) {
                $pivotBatch[] = [
                    'participant_id' => $participantId,
                    'program_id'     => $programId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];

                if (count($pivotBatch) === self::BATCH_SIZE) {
                    DB::table('participant_program')->insert($pivotBatch);
                    $pivotBatch = [];
                }
            }
        }
        if (! empty($pivotBatch)) {
            DB::table('participant_program')->insert($pivotBatch);
        }
    }
}
