<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Affiliation;
use App\Models\Program;
use App\Models\ParticipantType;

class ParticipantSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

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
        $hasHeader = in_array('Documento', $headers, true);

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
                'Documento' => 0,
                'Nombres' => 1,
                'Apellidos' => 2,
                'Tipo de Estamento' => 3,
                'Correo' => 4,
                'Programa o Dependencia' => 5,
                'Tipo_progama' => 6,
                'Vinculacion' => 7,
            ];
        }

        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        $programHash = [];
        $programByNameHash = [];
        foreach (Program::all(['id', 'name', 'campus']) as $program) {
            $key = strtolower($program->name) . '|' . strtolower($program->campus ?? '');
            $programHash[$key] = $program->id;
            $nameKey = strtolower(trim($program->name));
            if (! isset($programByNameHash[$nameKey])) {
                $programByNameHash[$nameKey] = $program->id;
            }
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $aff) {
            $affiliationHash[strtolower($aff->name)] = $aff->id;
        }

        $typeHash = [];
        foreach (ParticipantType::all(['id', 'name']) as $type) {
            $typeHash[strtolower($type->name)] = ['id' => $type->id, 'name' => $type->name];
        }

        $defaultType = null;
        if (isset($typeHash['comunidad externa'])) {
            $defaultType = $typeHash['comunidad externa'];
        } elseif (! empty($typeHash)) {
            $defaultType = array_values($typeHash)[0];
        }

        $participantMap = [];
        $programMap = [];
        $typeMap = [];
        $affiliationMap = [];
        $emailToDocument = [];

        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $document = trim((string) ($get($rawValues, 'Documento') ?? ''));
            if ($document === '') {
                continue;
            }

            $firstName = ucwords(strtolower(trim((string) ($get($rawValues, 'Nombres') ?? ''))));
            $lastName = ucwords(strtolower(trim((string) ($get($rawValues, 'Apellidos') ?? ''))));
            $roleName = trim((string) ($get($rawValues, 'Tipo de Estamento') ?? ''));
            $emailRaw = $get($rawValues, 'Correo');
            $programName = $get($rawValues, 'Programa o Dependencia');
            $email = null;

            if ($emailRaw !== null && trim((string) $emailRaw) !== '') {
                $emailCandidate = strtolower(trim((string) $emailRaw));
                if (str_contains($emailCandidate, '@')) {
                    $existingDoc = $emailToDocument[$emailCandidate] ?? null;
                    if ($existingDoc !== null && $existingDoc !== $document) {
                        continue;
                    }
                    $emailToDocument[$emailCandidate] = $document;
                    $email = $emailCandidate;
                } elseif ($programName === null || trim((string) $programName) === '') {
                    $programName = $emailRaw;
                }
            }
            $affiliationType = $get($rawValues, 'Vinculacion');

            $typeId = null;
            if ($roleName !== '') {
                $roleKey = strtolower($roleName);
                if (! isset($typeHash[$roleKey])) {
                    $normalizedName = ucwords(strtolower($roleName));
                    $type = ParticipantType::create(['name' => $normalizedName]);
                    $typeHash[$roleKey] = ['id' => $type->id, 'name' => $type->name];
                }
                $typeId = $typeHash[$roleKey]['id'] ?? null;
            } elseif ($defaultType) {
                $typeId = $defaultType['id'];
            }

            $programId = null;
            if (! empty($programName)) {
                $parts = explode(' - ', (string) $programName, 2);
                $programKey = strtolower(trim($parts[0])) . '|' . strtolower(trim($parts[1] ?? ''));
                $programId = $programHash[$programKey]
                    ?? $programByNameHash[strtolower(trim($parts[0]))]
                    ?? null;
            }

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
                if ($email && empty($participantMap[$document]['email'])) {
                    $participantMap[$document]['email'] = $email;
                }
                if ($programId && ! in_array($programId, $programMap[$document] ?? [], true)) {
                    $programMap[$document][] = $programId;
                }
                if ($typeId && ! in_array($typeId, $typeMap[$document] ?? [], true)) {
                    $typeMap[$document][] = $typeId;
                }
                if ($affiliationId && ! in_array($affiliationId, $affiliationMap[$document] ?? [], true)) {
                    $affiliationMap[$document][] = $affiliationId;
                }
                continue;
            }

            $participantMap[$document] = [
                'document' => $document,
                'student_code' => null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email ?: null,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            $programMap[$document] = $programId ? [$programId] : [];
            $typeMap[$document] = $typeId ? [$typeId] : [];
            $affiliationMap[$document] = $affiliationId ? [$affiliationId] : [];
        }

        if (empty($participantMap)) {
            return;
        }

        $now = now()->toDateTimeString();
        $batch = [];
        $documents = [];

        foreach ($participantMap as $doc => $data) {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $batch[] = $data;
            $documents[] = $doc;

            if (count($batch) === self::BATCH_SIZE) {
                DB::table('participants')->insert($batch);
                $batch = [];
            }
        }
        if (! empty($batch)) {
            DB::table('participants')->insert($batch);
        }

        $docToId = DB::table('participants')
            ->whereIn('document', $documents)
            ->pluck('id', 'document')
            ->toArray();

        $programBatch = [];
        $typeBatch = [];
        $affiliationBatch = [];

        foreach ($documents as $doc) {
            $participantId = $docToId[$doc] ?? null;
            if (! $participantId) {
                continue;
            }

            foreach (array_unique($programMap[$doc] ?? []) as $programId) {
                if (! $programId) {
                    continue;
                }
                $programBatch[] = [
                    'participant_id' => $participantId,
                    'program_id' => $programId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($programBatch) === self::BATCH_SIZE) {
                    DB::table('participant_program')->insertOrIgnore($programBatch);
                    $programBatch = [];
                }
            }

            foreach (array_unique($typeMap[$doc] ?? []) as $typeId) {
                if (! $typeId) {
                    continue;
                }
                $typeBatch[] = [
                    'participant_id' => $participantId,
                    'participant_type_id' => $typeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($typeBatch) === self::BATCH_SIZE) {
                    DB::table('participant_type_participant')->insertOrIgnore($typeBatch);
                    $typeBatch = [];
                }
            }

            foreach (array_unique($affiliationMap[$doc] ?? []) as $affiliationId) {
                if (! $affiliationId) {
                    continue;
                }
                $affiliationBatch[] = [
                    'participant_id' => $participantId,
                    'affiliation_id' => $affiliationId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($affiliationBatch) === self::BATCH_SIZE) {
                    DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
                    $affiliationBatch = [];
                }
            }
        }

        if (! empty($programBatch)) {
            DB::table('participant_program')->insertOrIgnore($programBatch);
        }
        if (! empty($typeBatch)) {
            DB::table('participant_type_participant')->insertOrIgnore($typeBatch);
        }
        if (! empty($affiliationBatch)) {
            DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
        }
    }
}
