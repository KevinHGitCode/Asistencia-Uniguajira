<?php

namespace App\Services;

use App\Exceptions\ImportParseException;
use App\Http\Controllers\Configuration\ProgramController;
use App\Models\Affiliation;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Support\ImportContext;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Parsea un archivo de importación de participantes y lo deja en staging
 * (`import_batches` / `staged_participants`) SIN tocar las tablas principales.
 *
 * La lógica vivía dentro de ParticipantImportController::import; se extrajo aquí
 * para poder ejecutarla tanto en el request (archivos pequeños) como dentro de un
 * job en cola (archivos .xlsx grandes — ADR-0004). El commit real ocurre al
 * aprobar el lote, en el controlador.
 */
class ParticipantImportParser
{
    private const BATCH_SIZE = 500;

    private const REQUIRED_COLUMNS = [
        'Documento',
        'Nombres',
        'Apellidos',
        'Tipo de Estamento',
        'Correo',
        'Programa o Dependencia',
        'Vinculacion',
    ];

    private const PROGRAM_TYPES = ['pregrado', 'posgrado', 'postgrado'];

    /**
     * Estamentos que deben ligarse a una Dependencia (no a un Programa).
     */
    private const DEPENDENCY_ROLE_KEYS = ['administrativo'];

    /**
     * Parsea el archivo y rellena el lote (ya creado en estado `procesando`).
     * Al terminar deja el lote en `en_revision` con sus contadores.
     *
     * @throws ImportParseException si el archivo está vacío o le faltan columnas.
     */
    public function parse(ImportBatch $batch, string $path, string $extension, ImportContext $ctx): void
    {
        $startedAt = microtime(true);

        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        DB::connection()->disableQueryLog();

        $extension = strtolower($extension);

        if (in_array($extension, ['csv', 'txt'], true)) {
            $allRows = $this->readCsvRows($path);
        } else {
            $sheets = Excel::toArray([], $path);
            $allRows = $sheets[0] ?? [];
        }

        if (empty($allRows)) {
            throw new ImportParseException('El archivo está vacío.');
        }

        // ── Leer y validar cabeceras ──────────────────────────────────────
        $headerRow = array_values((array) $allRows[0]);
        $headers = array_map(fn ($h) => trim((string) ($h ?? '')), $headerRow);

        $colIndex = [];
        foreach ($headers as $pos => $name) {
            if ($name !== '') {
                $colIndex[$name] = $pos;
            }
        }

        $missing = array_values(array_filter(
            self::REQUIRED_COLUMNS,
            fn ($col) => ! isset($colIndex[$col])
        ));

        if (! empty($missing)) {
            throw new ImportParseException(
                'El archivo no tiene las siguientes columnas requeridas: '
                .implode(', ', array_map(fn ($c) => "«{$c}»", $missing))
                .'. Descarga la plantilla oficial y vuelve a intentarlo.'
            );
        }

        $defaultCampusId = $ctx->defaultCampusId;

        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        array_shift($allRows);
        $rows = $allRows;

        // ── Cachés de lookup ──────────────────────────────────────────────
        $campusByNameHash = Campus::orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn (Campus $campus) => [ProgramController::comparisonKey($campus->name) => $campus->id])
            ->all();

        $programByCampusAndNameHash = [];
        $programIdsByNameHash = [];
        foreach (Program::with('academicProgram:id,name')->get(['id', 'name', 'campus_id', 'academic_program_id']) as $p) {
            if (! $p->campus_id) {
                continue;
            }

            foreach ($this->programLookupKeys($p->name, (int) $p->campus_id, $campusByNameHash) as $key) {
                if (! isset($programByCampusAndNameHash[$p->campus_id][$key])) {
                    $programByCampusAndNameHash[$p->campus_id][$key] = $p->id;
                }
                $programIdsByNameHash[$key][$p->id] = true;
            }
            if ($p->academicProgram && ! isset($programByCampusAndNameHash[$p->campus_id][ProgramController::comparisonKey($p->academicProgram->name)])) {
                $programByCampusAndNameHash[$p->campus_id][ProgramController::comparisonKey($p->academicProgram->name)] = $p->id;
            }
            if ($p->academicProgram) {
                $academicKey = $this->programLookupKey($p->academicProgram->name);
                $programIdsByNameHash[$academicKey][$p->id] = true;
            }
        }

        $dependencyByCampusAndNameHash = [];
        $dependencyIdsByNameHash = [];
        foreach (Dependency::all(['id', 'name', 'campus_id']) as $d) {
            if ($d->campus_id) {
                $keys = array_unique([
                    ProgramController::comparisonKey($d->name),
                    $this->dependencyLookupKey($d->name, (int) $d->campus_id, $campusByNameHash),
                ]);

                foreach ($keys as $key) {
                    $dependencyByCampusAndNameHash[$d->campus_id][$key] = $d->id;
                    $dependencyIdsByNameHash[$key][$d->id] = true;
                }
            }
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $a) {
            $affiliationHash[ProgramController::comparisonKey($a->name)] = $a->id;
        }

        $typeHash = [];
        foreach (ParticipantType::all(['id', 'name']) as $t) {
            $typeHash[ProgramController::comparisonKey($t->name)] = ['id' => $t->id, 'name' => $t->name];
        }

        // ── Pre-escaneo del archivo para limitar las consultas a BD ───────
        $excelDocs = [];
        $excelEmails = [];

        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $doc = trim((string) ($get($rawValues, 'Documento') ?? ''));
            if ($doc !== '') {
                $excelDocs[$doc] = true;
            }

            $emailRaw = self::normalizeExcelText($get($rawValues, 'Correo'));
            if ($emailRaw !== '') {
                $excelEmails[mb_strtolower($emailRaw, 'UTF-8')] = true;
            }
        }

        $existingDocToId = [];
        foreach (array_chunk(array_keys($excelDocs), 500) as $docChunk) {
            $existingDocToId += DB::table('participants')
                ->whereIn('document', $docChunk)
                ->pluck('id', 'document')
                ->toArray();
        }

        $existingEmails = [];
        foreach (array_chunk(array_keys($excelEmails), 500) as $emailChunk) {
            $emails = DB::table('participants')
                ->whereNotNull('email')
                ->whereIn('email', $emailChunk)
                ->pluck('email')
                ->toArray();

            foreach ($emails as $e) {
                $existingEmails[$e] = true;
            }
        }

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newRoles = [];

        $excelRolesForExisting = [];
        $skipped = [];

        // ── Primera pasada: clasificar cada fila ──────────────────────────
        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $document = trim((string) ($get($rawValues, 'Documento') ?? ''));
            $firstNameRaw = self::normalizeExcelText($get($rawValues, 'Nombres'));
            $lastNameRaw = self::normalizeExcelText($get($rawValues, 'Apellidos'));
            $roleName = self::normalizeExcelText($get($rawValues, 'Tipo de Estamento'));
            $emailRaw = self::normalizeExcelText($get($rawValues, 'Correo'));
            $programName = self::normalizeExcelText($get($rawValues, 'Programa o Dependencia'));
            $programTypeRaw = self::normalizeExcelText($get($rawValues, 'Tipo_progama'));
            $affiliationType = self::normalizeExcelText($get($rawValues, 'Vinculacion'));

            $firstName = $firstNameRaw === ''
                ? ''
                : mb_convert_case(mb_strtolower($firstNameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $lastName = $lastNameRaw === ''
                ? ''
                : mb_convert_case(mb_strtolower($lastNameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $email = $emailRaw !== ''
                ? mb_strtolower($emailRaw, 'UTF-8')
                : null;

            if ($document === '') {
                $skipped[] = $this->skippedRow($rawValues, $headers, 'Documento vacío');

                continue;
            }

            $rowCampusId = $this->resolveImportCampusId(
                $ctx,
                $defaultCampusId,
                $this->campusIdFromNameSuffix($programName, $campusByNameHash),
            );
            // ── Validar tipo de estamento ─────────────────────────────────
            $roleKey = ProgramController::comparisonKey($roleName);
            $typeData = $typeHash[$roleKey] ?? null;
            if (! $typeData) {
                $skipped[] = $this->skippedRow(
                    $rawValues, $headers,
                    $roleName === ''
                        ? 'Tipo de Estamento vacío'
                        : "Tipo de Estamento no válido: \"{$roleName}\""
                );

                continue;
            }
            $typeId = $typeData['id'];

            $isDependencyRole = in_array(
                $roleKey,
                self::DEPENDENCY_ROLE_KEYS,
                true
            );

            $isProgramType = in_array(
                mb_strtolower($programTypeRaw, 'UTF-8'),
                self::PROGRAM_TYPES,
                true
            );

            $programId = null;
            $dependencyId = null;

            if ($isDependencyRole) {
                if ($programName === '') {
                    $skipped[] = $this->skippedRow(
                        $rawValues, $headers,
                        'Dependencia vacía para estamento Administrativo'
                    );

                    continue;
                }

                $nameKey = $this->dependencyLookupKey($programName, $rowCampusId, $campusByNameHash);
                $dependencyId = $rowCampusId
                    ? ($dependencyByCampusAndNameHash[$rowCampusId][$nameKey] ?? null)
                    : $this->uniqueCatalogId($dependencyIdsByNameHash[$nameKey] ?? []);

                if (! $dependencyId) {
                    $reason = ! $rowCampusId && isset($dependencyIdsByNameHash[$nameKey])
                        ? "No se pudo determinar la sede de la dependencia \"{$programName}\". Agrega el sufijo \"- Sede\" en Programa o Dependencia."
                        : "Dependencia no encontrada: \"{$programName}\"";

                    $skipped[] = $this->skippedRow(
                        $rawValues, $headers,
                        $reason
                    );

                    continue;
                }
            } elseif ($programName !== '') {
                $rawProgramName = $programName;
                $programCampusId = $rowCampusId
                    ?? $this->campusIdFromNameSuffix($rawProgramName, $campusByNameHash);
                $programKeys = $this->programLookupKeys($rawProgramName, $programCampusId, $campusByNameHash);
                $nameKey = $programKeys[0];

                if ($isProgramType) {
                    if ($programCampusId) {
                        $programsForCampus = $programByCampusAndNameHash[$programCampusId] ?? [];
                        foreach ($programKeys as $key) {
                            $programId = $programsForCampus[$key] ?? null;
                            if ($programId) {
                                break;
                            }
                        }
                        $programId ??= $this->findClosestProgramId($rawProgramName, $programsForCampus);
                    } else {
                        $candidateProgramIds = [];
                        foreach ($programKeys as $key) {
                            $candidateProgramIds += $programIdsByNameHash[$key] ?? [];
                        }
                        $programId = $this->uniqueCatalogId($candidateProgramIds);
                    }

                    if (! $programId) {
                        $hasProgramCandidate = collect($programKeys)
                            ->contains(fn (string $key) => isset($programIdsByNameHash[$key]));
                        $reason = ! $rowCampusId && $hasProgramCandidate
                            ? "No se pudo determinar la sede del programa \"{$rawProgramName}\". Agrega el sufijo \"- Sede\" en Programa o Dependencia."
                            : "Programa no encontrado: \"{$rawProgramName}\"";

                        $skipped[] = $this->skippedRow(
                            $rawValues, $headers,
                            $reason
                        );

                        continue;
                    }
                } else {
                    $nameKey = $this->dependencyLookupKey($programName, $rowCampusId, $campusByNameHash);
                    $dependencyId = $rowCampusId
                        ? ($dependencyByCampusAndNameHash[$rowCampusId][$nameKey] ?? null)
                        : $this->uniqueCatalogId($dependencyIdsByNameHash[$nameKey] ?? []);
                }
            }

            // ── Resolver vinculación ──────────────────────────────────────
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

            // ── Construir clave compuesta del rol ─────────────────────────
            $compositeKey = ($typeId ?? 0).'|'.($programId ?? 0).'|'.($dependencyId ?? 0).'|'.($affiliationId ?? 0);
            $roleData = [
                'participant_type_id' => $typeId,
                'program_id' => $programId,
                'dependency_id' => $dependencyId,
                'affiliation_id' => $affiliationId,
            ];

            // ── 1) Doc ya visto en este archivo (nuevo) ───────────────────
            if (isset($newParticipants[$document])) {
                $newRoles[$document][$compositeKey] = $roleData;

                continue;
            }

            // ── 2) Doc ya existe en BD ────────────────────────────────────
            if (isset($existingDocToId[$document])) {
                $pid = $existingDocToId[$document];
                $excelRolesForExisting[$pid][$compositeKey] = $roleData;

                continue;
            }

            // ── 3) Email duplicado ────────────────────────────────────────
            if ($email !== null && isset($existingEmails[$email])) {
                $skipped[] = $this->skippedRow($rawValues, $headers, "Correo duplicado ({$email})");

                continue;
            }

            // ── 4) Nuevo participante ─────────────────────────────────────
            $newParticipants[$document] = [
                'document' => $document,
                'student_code' => null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $newRoles[$document] = [$compositeKey => $roleData];

            if ($email) {
                $existingEmails[$email] = true;
            }
        }

        // ── Pasarela de revisión: guardar el plan en staging ──────────────
        $this->persistStaging($batch, $newParticipants, $newRoles, $excelRolesForExisting, $skipped);

        $batch->update([
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        ActivityLogService::log(
            'importar',
            'participantes',
            "Cargó un lote de importación (#{$batch->id}) para revisión",
            $batch,
            [
                'new' => $batch->new_count,
                'update' => $batch->update_count,
                'skipped' => $batch->skipped_count,
            ],
            $ctx->userId,
        );
    }

    /**
     * Rellena el lote (ya creado) con el plan calculado y lo deja en
     * `en_revision`. NO toca las tablas principales.
     */
    private function persistStaging(
        ImportBatch $batch,
        array $newParticipants,
        array $newRoles,
        array $excelRolesForExisting,
        array $skipped,
    ): void {
        DB::beginTransaction();

        try {
            $now = now();

            $batch->update([
                'status' => 'en_revision',
                'total_rows' => count($newParticipants) + count($excelRolesForExisting) + count($skipped),
                'new_count' => count($newParticipants),
                'update_count' => count($excelRolesForExisting),
                'skipped_count' => count($skipped),
            ]);

            $buffer = [];
            $flush = function () use (&$buffer) {
                if (! empty($buffer)) {
                    DB::table('staged_participants')->insert($buffer);
                    $buffer = [];
                }
            };

            foreach ($newParticipants as $doc => $data) {
                $buffer[] = [
                    'import_batch_id' => $batch->id,
                    'status' => 'nuevo',
                    'document' => $doc,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'email' => $data['email'] ?? null,
                    'existing_participant_id' => null,
                    'roles' => json_encode(array_values($newRoles[$doc] ?? [])),
                    'error' => null,
                    'raw' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($buffer) >= self::BATCH_SIZE) {
                    $flush();
                }
            }

            $existingIds = array_keys($excelRolesForExisting);
            $existingInfo = Participant::whereIn('id', $existingIds)
                ->get(['id', 'document', 'first_name', 'last_name'])
                ->keyBy('id');

            foreach ($excelRolesForExisting as $pid => $wantedRoles) {
                $info = $existingInfo->get($pid);
                $buffer[] = [
                    'import_batch_id' => $batch->id,
                    'status' => 'actualiza',
                    'document' => $info?->document,
                    'first_name' => $info?->first_name,
                    'last_name' => $info?->last_name,
                    'email' => null,
                    'existing_participant_id' => $pid,
                    'roles' => json_encode(array_values($wantedRoles)),
                    'error' => null,
                    'raw' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($buffer) >= self::BATCH_SIZE) {
                    $flush();
                }
            }

            foreach ($skipped as $row) {
                $buffer[] = [
                    'import_batch_id' => $batch->id,
                    'status' => 'omitido',
                    'document' => $row['Documento'] ?? null,
                    'first_name' => $row['Nombres'] ?? null,
                    'last_name' => $row['Apellidos'] ?? null,
                    'email' => $row['Correo'] ?? null,
                    'existing_participant_id' => null,
                    'roles' => null,
                    'error' => $row['_motivo'] ?? null,
                    'raw' => json_encode($row),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (count($buffer) >= self::BATCH_SIZE) {
                    $flush();
                }
            }

            $flush();

            DB::commit();

            $batch->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function uniqueCatalogId(array $ids): ?int
    {
        $ids = array_keys($ids);

        return count($ids) === 1 ? (int) $ids[0] : null;
    }

    private function resolveImportCampusId(ImportContext $ctx, ?int $defaultCampusId, ?int $suffixCampusId): ?int
    {
        $campusId = $defaultCampusId ?? $suffixCampusId;

        if (! $campusId) {
            return null;
        }

        if (! $ctx->isSuperadmin && (int) $ctx->userCampusId !== (int) $campusId) {
            return null;
        }

        return (int) $campusId;
    }

    /**
     * Lee un CSV de forma nativa (rápido) devolviendo filas como arrays
     * numéricos, equivalente a lo que entrega `Excel::toArray()[0]`.
     */
    private function readCsvRows(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || $content === '') {
            return [];
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $delimiter = $this->detectCsvDelimiter($content);

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $rows[] = $data;
        }

        fclose($handle);

        return $rows;
    }

    private function detectCsvDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: '';

        $counts = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
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
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã' => 'Í',
            'Ã“' => 'Ó',
            'Ãš' => 'Ú',
            'Ã±' => 'ñ',
            'Ã‘' => 'Ñ',
            'Ã¼' => 'ü',
            'Ãœ' => 'Ü',
            'Â' => '',
        ]);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    private function programLookupKey(string $programName): string
    {
        $normalizedSeparator = preg_replace('/\s*-\s*/u', ' - ', trim($programName)) ?? $programName;

        return ProgramController::comparisonKey($normalizedSeparator);
    }

    /**
     * @return array<int, string>
     */
    private function programLookupKeys(string $programName, ?int $campusId, array $campusByNameHash): array
    {
        $keys = [$this->programLookupKey($programName)];

        if (! preg_match('/\s*-\s*([^-]+)\s*$/u', trim($programName), $matches)) {
            return $keys;
        }

        $suffixCampusId = $campusByNameHash[ProgramController::comparisonKey($matches[1])] ?? null;
        if (! $suffixCampusId || ($campusId !== null && (int) $suffixCampusId !== $campusId)) {
            return $keys;
        }

        $withoutCampusSuffix = preg_replace('/\s*-\s*[^-]+\s*$/u', '', trim($programName));
        if ($withoutCampusSuffix !== null && $withoutCampusSuffix !== '') {
            $keys[] = $this->programLookupKey($withoutCampusSuffix);
        }

        return array_values(array_unique($keys));
    }

    private function campusIdFromNameSuffix(string $name, array $campusByNameHash): ?int
    {
        if (! preg_match('/\s*-\s*([^-]+)\s*$/u', trim($name), $matches)) {
            return null;
        }

        $campusId = $campusByNameHash[ProgramController::comparisonKey($matches[1])] ?? null;

        return $campusId ? (int) $campusId : null;
    }

    private function dependencyLookupKey(string $dependencyName, ?int $rowCampusId, array $campusByNameHash): string
    {
        $key = ProgramController::comparisonKey($dependencyName);

        if (! preg_match('/\s*-\s*([^-]+)\s*$/u', trim($dependencyName), $matches)) {
            return $key;
        }

        $suffixCampusId = $campusByNameHash[ProgramController::comparisonKey($matches[1])] ?? null;
        if (! $suffixCampusId || ($rowCampusId !== null && (int) $suffixCampusId !== $rowCampusId)) {
            return $key;
        }

        $withoutCampusSuffix = preg_replace('/\s*-\s*[^-]+\s*$/u', '', trim($dependencyName));

        return ProgramController::comparisonKey($withoutCampusSuffix ?? $dependencyName);
    }

    private function findClosestProgramId(string $programName, array $programByNameHash): ?int
    {
        $targetKey = $this->programLookupKey($programName);
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

    private function skippedRow(array $raw, array $headers, string $motivo): array
    {
        $row = [];
        foreach ($headers as $i => $header) {
            if ($header !== '') {
                $row[$header] = $raw[$i] ?? null;
            }
        }
        $row['_motivo'] = $motivo;

        return $row;
    }
}
