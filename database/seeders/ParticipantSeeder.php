<?php

namespace Database\Seeders;

use App\Http\Controllers\Configuration\ProgramController;
use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    private const PROGRAM_TYPES = ['pregrado', 'posgrado', 'postgrado'];

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
                'Documento' => 0, 'Nombres' => 1, 'Apellidos' => 2,
                'Tipo de Estamento' => 3, 'Correo' => 4,
                'Programa o Dependencia' => 5, 'Tipo_progama' => 6, 'Vinculacion' => 7,
            ];
        }

        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        // ── Cachés ────────────────────────────────────────────────────────
        $programByNameHash = [];
        foreach (Program::all(['id', 'name']) as $p) {
            $k = ProgramController::comparisonKey($p->name);
            if (! isset($programByNameHash[$k])) {
                $programByNameHash[$k] = $p->id;
            }
        }

        $dependencyHash = [];
        foreach (Dependency::all(['id', 'name']) as $d) {
            $dependencyHash[ProgramController::comparisonKey($d->name)] = $d->id;
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $a) {
            $affiliationHash[ProgramController::comparisonKey($a->name)] = $a->id;
        }

        $typeHash = [];
        foreach (ParticipantType::all(['id', 'name']) as $t) {
            $typeHash[ProgramController::comparisonKey($t->name)] = ['id' => $t->id, 'name' => $t->name];
        }

        $defaultType = $typeHash[ProgramController::comparisonKey('Comunidad Externa')]
            ?? (! empty($typeHash) ? array_values($typeHash)[0] : null);

        $participantMap  = [];
        $rolesMap        = [];
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

            $firstNameRaw    = self::normalizeExcelText($get($rawValues, 'Nombres'));
            $lastNameRaw     = self::normalizeExcelText($get($rawValues, 'Apellidos'));
            $roleName        = self::normalizeExcelText($get($rawValues, 'Tipo de Estamento'));
            $emailRaw        = self::normalizeExcelText($get($rawValues, 'Correo'));
            $programName     = self::normalizeExcelText($get($rawValues, 'Programa o Dependencia'));
            $programTypeRaw  = self::normalizeExcelText($get($rawValues, 'Tipo_progama'));
            $affiliationType = self::normalizeExcelText($get($rawValues, 'Vinculacion'));
            $email           = null;
            $firstName       = $firstNameRaw === ''
                ? ''
                : mb_convert_case(mb_strtolower($firstNameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $lastName        = $lastNameRaw === ''
                ? ''
                : mb_convert_case(mb_strtolower($lastNameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

            if ($emailRaw !== '') {
                $emailCandidate = mb_strtolower($emailRaw, 'UTF-8');
                if (str_contains($emailCandidate, '@')) {
                    $existingDoc = $emailToDocument[$emailCandidate] ?? null;
                    if ($existingDoc !== null && $existingDoc !== $document) {
                        continue;
                    }
                    $emailToDocument[$emailCandidate] = $document;
                    $email = $emailCandidate;
                } elseif ($programName === '') {
                    $programName = $emailRaw;
                }
            }

            // ── Tipo de estamento ─────────────────────────────────────────
            $typeId = null;
            if ($roleName !== '') {
                $roleKey = ProgramController::comparisonKey($roleName);
                if (! isset($typeHash[$roleKey])) {
                    $type = ParticipantType::create([
                        'name' => mb_convert_case(mb_strtolower($roleName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                    ]);
                    $typeHash[$roleKey] = ['id' => $type->id, 'name' => $type->name];
                }
                $typeId = $typeHash[$roleKey]['id'];
            } elseif ($defaultType) {
                $typeId = $defaultType['id'];
            }

            // ── Programa o Dependencia ────────────────────────────────────
            $isProgramType = in_array(
                mb_strtolower($programTypeRaw, 'UTF-8'),
                self::PROGRAM_TYPES,
                true
            );

            $programId    = null;
            $dependencyId = null;

            if ($programName !== '') {
                $rawName = $programName;
                $nameKey = ProgramController::comparisonKey($rawName);

                if ($isProgramType) {
                    $programId = $programByNameHash[$nameKey]
                        ?? $this->findClosestProgramId($rawName, $programByNameHash);
                } else {
                    if (! isset($dependencyHash[$nameKey])) {
                        $cleanName = preg_replace('/\s+/u', ' ', $programName);
                        $dep = Dependency::create(['name' => ProgramController::normalizeName($cleanName)]);
                        $dependencyHash[$nameKey] = $dep->id;
                    }
                    $dependencyId = $dependencyHash[$nameKey];
                }
            }

            // ── Vinculación ───────────────────────────────────────────────
            $affiliationId = null;
            if ($affiliationType !== '' && $affiliationType !== '0' && $affiliationType !== 0) {
                $affKey = ProgramController::comparisonKey($affiliationType);
                if (! isset($affiliationHash[$affKey])) {
                    $cleanName = preg_replace('/\s+/u', ' ', $affiliationType);
                    $aff = Affiliation::create(['name' => $cleanName]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── Construir el rol ──────────────────────────────────────────
            $roleKey = ($typeId ?? 0) . '|' . ($programId ?? 0) . '|' . ($dependencyId ?? 0) . '|' . ($affiliationId ?? 0);

            if (! isset($rolesMap[$document])) {
                $rolesMap[$document] = [];
            }
            if (! isset($rolesMap[$document][$roleKey])) {
                $rolesMap[$document][$roleKey] = [
                    'participant_type_id' => $typeId,
                    'program_id'          => $programId,
                    'dependency_id'       => $dependencyId,
                    'affiliation_id'      => $affiliationId,
                ];
            }

            // ── Participante (primera aparición) ──────────────────────────
            if (isset($participantMap[$document])) {
                if ($email && empty($participantMap[$document]['email'])) {
                    $participantMap[$document]['email'] = $email;
                }
                continue;
            }

            $participantMap[$document] = [
                'document'     => $document,
                'student_code' => null,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => $email ?: null,
                'created_at'   => now()->toDateTimeString(),
                'updated_at'   => now()->toDateTimeString(),
            ];
        }

        if (empty($participantMap)) {
            return;
        }

        $now       = now()->toDateTimeString();
        $batch     = [];
        $documents = [];

        foreach ($participantMap as $doc => $data) {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $batch[]     = $data;
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

        $roleBatch = [];

        foreach ($documents as $doc) {
            $pid = $docToId[$doc] ?? null;
            if (! $pid) {
                continue;
            }

            foreach ($rolesMap[$doc] ?? [] as $role) {
                $roleBatch[] = [
                    'participant_id'      => $pid,
                    'participant_type_id' => $role['participant_type_id'],
                    'program_id'          => $role['program_id'],
                    'dependency_id'       => $role['dependency_id'],
                    'affiliation_id'      => $role['affiliation_id'],
                    'is_active'           => true,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                if (count($roleBatch) === self::BATCH_SIZE) {
                    DB::table('participant_roles')->insert($roleBatch);
                    $roleBatch = [];
                }
            }
        }

        if (! empty($roleBatch)) {
            DB::table('participant_roles')->insert($roleBatch);
        }
    }

    private static function normalizeExcelText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = strtr($text, [
            'Ã¡' => 'á',
            'Ã©' => 'é',
            'Ã­' => 'í',
            'Ã³' => 'ó',
            'Ãº' => 'ú',
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã' => 'Í',
            'Ã“' => 'Ó',
            'Ãš' => 'Ú',
            'Ã±' => 'ñ',
            'Ã‘' => 'Ñ',
            'Ã¼' => 'ü',
            'Ãœ' => 'Ü',
            'Â'  => '',
        ]);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    private function findClosestProgramId(string $programName, array $programByNameHash): ?int
    {
        $targetKey = ProgramController::comparisonKey($programName);
        $targetCompact = preg_replace('/[^a-z0-9]+/i', '', $targetKey) ?? '';

        if ($targetCompact === '') {
            return null;
        }

        $bestId = null;
        $bestDistance = PHP_INT_MAX;

        foreach ($programByNameHash as $key => $id) {
            $candidateCompact = preg_replace('/[^a-z0-9]+/i', '', (string) $key) ?? '';
            if ($candidateCompact === '') {
                continue;
            }

            $distance = levenshtein($targetCompact, $candidateCompact);
            if ($distance <= 3 && $distance < $bestDistance) {
                $bestDistance = $distance;
                $bestId = $id;
            }
        }

        return $bestId;
    }
}
