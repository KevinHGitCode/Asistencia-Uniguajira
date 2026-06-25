<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Services\ActivityLogService;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImportController extends Controller
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
     * Se compara por comparisonKey (lowercase, sin acentos, whitespace colapsado).
     */
    private const DEPENDENCY_ROLE_KEYS = ['administrativo'];

    public function index(CampusScopeService $campusScope)
    {
        $programs = $campusScope->applyToQuery(Program::query(), request()->user())
            ->orderBy('name')->get(['id', 'name']);
        $dependencies = $campusScope->applyToQuery(Dependency::query(), request()->user())
            ->orderBy('name')->get(['id', 'name']);
        $affiliations = Affiliation::orderBy('name')->get(['id', 'name']);
        $estamentos = ParticipantType::orderBy('name')->get(['id', 'name']);

        // Total de participantes (globales; no se filtran por sede) para mostrarlo
        // bajo el título y que se distinga de un vistazo si hay datos o no.
        $participantsCount = Participant::count();

        // Lotes de importación pendientes de revisión (pasarela ADR-0004).
        $pendingBatches = ImportBatch::where('status', 'en_revision')
            ->latest()
            ->take(5)
            ->get();

        return view('administration.participants.index', compact('programs', 'dependencies', 'affiliations', 'estamentos', 'participantsCount', 'pendingBatches'));
    }

    public function downloadTemplate()
    {
        ActivityLogService::log('exportar', 'participantes', 'Descargó la plantilla de importación de participantes');

        return Excel::download(
            new \App\Exports\ParticipantTemplateExport,
            'plantilla_participantes.xlsx'
        );
    }

    public function import(Request $request, CampusScopeService $campusScope)
    {
        $startedAt = microtime(true);

        set_time_limit(0);
        // Subimos el límite de memoria. Si el servidor ignora este ini_set
        // (por ejemplo por configuración de hosting), igual el resto del
        // método está pensado para no cargar toda la tabla participants en
        // memoria de una sola vez.
        ini_set('memory_limit', '1024M');

        // El cargue no necesita el query log; desactivarlo evita que la memoria
        // crezca con cada consulta en archivos grandes.
        DB::connection()->disableQueryLog();

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.mimes' => 'El archivo debe ser .xlsx, .xls o .csv.',
            'excel_file.max' => 'El archivo no debe superar los 20 MB.',
            'excel_file.uploaded' => 'No se pudo subir el archivo Excel. Verifica el tamano del archivo y vuelve a intentarlo.',
        ], [
            'excel_file' => 'archivo Excel',
        ]);

        // ── Lectura del archivo ───────────────────────────────────────────
        // Fast-path para CSV: `fgetcsv` nativo es mucho más rápido que
        // PhpSpreadsheet. Para .xlsx/.xls se mantiene maatwebsite/excel.
        $uploaded = $request->file('excel_file');
        $extension = strtolower($uploaded->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            $allRows = $this->readCsvRows($uploaded->getRealPath());
        } else {
            $sheets = Excel::toArray([], $uploaded);
            $allRows = $sheets[0] ?? [];
        }

        if (empty($allRows)) {
            return back()->withErrors(['excel_file' => 'El archivo está vacío.']);
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
            return back()->withErrors([
                'excel_file' => 'El archivo no tiene las siguientes columnas requeridas: '
                    .implode(', ', array_map(fn ($c) => "«{$c}»", $missing))
                    .'. Descarga la plantilla oficial y vuelve a intentarlo.',
            ]);
        }

        $defaultCampusId = $this->defaultCampusIdForImport($request, $campusScope);

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

        // ── Pre-escaneo del Excel para saber qué documentos/correos
        //    aparecen realmente en el archivo. Así las consultas a BD solo
        //    traen los registros relevantes, en lugar de toda la tabla
        //    participants (que en producción podría tener decenas de miles
        //    de filas y agotar la memoria en fetchAll). ───────────────────
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

        // ── Solo pedimos a la BD los participantes cuyos documentos están
        //    en el Excel. El whereIn se trocea en lotes para no superar el
        //    límite de parámetros de SQLite (SQLITE_MAX_VARIABLE_NUMBER).
        $existingDocToId = [];
        foreach (array_chunk(array_keys($excelDocs), 500) as $docChunk) {
            $existingDocToId += DB::table('participants')
                ->whereIn('document', $docChunk)
                ->pluck('id', 'document')
                ->toArray();
        }

        // ── Correos ya existentes. Solo interesa conocer los correos que
        //    podrían colisionar con los del Excel; no tiene sentido cargar
        //    la tabla entera. ─────────────────────────────────────────────
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

        // Nota: los roles activos de los participantes existentes ya NO se
        // consultan aquí. El commit ocurre al APROBAR el lote y `commitPlan`
        // recalcula los roles activos en ese momento (estado fresco), así que
        // hacerlo en el parseo era trabajo y memoria desperdiciados.

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
                $request,
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

            // ── Determinar si este estamento se liga a Dependencia en vez
            //    de a Programa (caso: Administrativo). ──────────────────────
            $isDependencyRole = in_array(
                $roleKey,
                self::DEPENDENCY_ROLE_KEYS,
                true
            );

            // Para el resto de estamentos se decide por la columna Tipo_progama
            $isProgramType = in_array(
                mb_strtolower($programTypeRaw, 'UTF-8'),
                self::PROGRAM_TYPES,
                true
            );

            $programId = null;
            $dependencyId = null;

            if ($isDependencyRole) {
                // ── Estamento que se liga a Dependencia (Administrativo) ──
                // Si la dependencia viene vacía o no existe en BD, se salta
                // la fila y se reporta en el Excel de omitidos (mismo criterio
                // que cuando un programa no existe para Estudiante/Docente).
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
                    // TEMPORAL: Creación de dependencias desde el cargue masivo
                    // de participantes deshabilitada. Si la dependencia ya existe
                    // en BD se asocia; si no, el rol queda sin dependencia.
                    // if (! isset($dependencyHash[$nameKey])) {
                    //     // Misma normalización que usa el cargue masivo de dependencias:
                    //     // trim, whitespace colapsado y primera letra en mayúscula
                    //     // (resto en minúsculas), con soporte UTF-8.
                    //     $cleanName = DependencyController::normalizeName($programName);
                    //     $dep = Dependency::create(['name' => $cleanName]);
                    //     $dependencyHash[$nameKey] = $dep->id;
                    // }
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

        // ══════════════════════════════════════════════════════════════════
        // ── Pasarela de revisión (ADR-0004): NO se toca la tabla principal.
        //    Se guarda el plan en staging y el commit real ocurre al APROBAR.
        // ══════════════════════════════════════════════════════════════════
        $importBatch = $this->persistStaging(
            $request->file('excel_file')->getClientOriginalName(),
            $newParticipants,
            $newRoles,
            $excelRolesForExisting,
            $skipped,
        );

        // Medición interna (solo BD) del tiempo de procesamiento del cargue.
        $importBatch->update([
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        ActivityLogService::log(
            'importar',
            'participantes',
            "Cargó un lote de importación (#{$importBatch->id}) para revisión",
            $importBatch,
            [
                'new' => $importBatch->new_count,
                'update' => $importBatch->update_count,
                'skipped' => $importBatch->skipped_count,
            ],
        );

        return redirect()
            ->route('participants-import.review', $importBatch)
            ->with('success', 'Archivo procesado. Revisa los registros antes de confirmar: nada se guarda hasta que apruebes el lote.');
    }

    /**
     * Guarda el plan calculado (nuevos, actualizaciones y omitidos) en las
     * tablas de staging para revisión. NO toca las tablas principales.
     */
    private function persistStaging(
        string $filename,
        array $newParticipants,
        array $newRoles,
        array $excelRolesForExisting,
        array $skipped,
    ): ImportBatch {
        // Una sola transacción para todos los inserts del staging: en SQLite
        // evita un fsync por sentencia y acelera mucho el guardado.
        DB::beginTransaction();

        try {
            $now = now();

            $batch = ImportBatch::create([
                'user_id' => auth()->id(),
                'original_filename' => $filename,
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

            // Nuevos participantes (agregados por documento)
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

            // Actualizaciones a participantes existentes
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

            // Filas omitidas (una por fila del Excel)
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

            return $batch;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Aplica el plan a las tablas principales. Mismo comportamiento que el
     * importador original, pero ejecutado al APROBAR un lote ya revisado.
     * Recalcula los roles activos en el momento del commit.
     */
    private function commitPlan(array $newParticipants, array $newRoles, array $excelRolesForExisting): array
    {
        $now = now()->toDateTimeString();

        // ── Insertar nuevos participantes ─────────────────────────────────
        $saved = 0;
        $batch = [];
        $docKeys = [];

        foreach ($newParticipants as $doc => $data) {
            $batch[] = $data;
            $docKeys[] = $doc;
            if (count($batch) === self::BATCH_SIZE) {
                DB::table('participants')->insert($batch);
                $saved += self::BATCH_SIZE;
                $batch = [];
            }
        }
        if (! empty($batch)) {
            DB::table('participants')->insert($batch);
            $saved += count($batch);
        }

        // ── Roles para nuevos participantes ───────────────────────────────
        if (! empty($docKeys)) {
            $docToId = DB::table('participants')
                ->whereIn('document', $docKeys)
                ->pluck('id', 'document')
                ->toArray();

            $roleBatch = [];

            foreach ($docKeys as $doc) {
                $pid = $docToId[$doc] ?? null;
                if (! $pid) {
                    continue;
                }

                foreach ($newRoles[$doc] ?? [] as $role) {
                    $roleBatch[] = [
                        'participant_id' => $pid,
                        'participant_type_id' => $role['participant_type_id'],
                        'program_id' => $role['program_id'],
                        'dependency_id' => $role['dependency_id'],
                        'affiliation_id' => $role['affiliation_id'],
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
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

        // ── Roles activos actuales de los participantes existentes ────────
        $activeRoles = [];
        $existingIds = array_keys($excelRolesForExisting);
        foreach (array_chunk($existingIds, 500) as $idChunk) {
            DB::table('participant_roles')
                ->whereIn('participant_id', $idChunk)
                ->where('is_active', 1)
                ->get(['id', 'participant_id', 'participant_type_id', 'program_id', 'dependency_id', 'affiliation_id'])
                ->each(function ($r) use (&$activeRoles) {
                    $key = ($r->participant_type_id ?? 0).'|'.($r->program_id ?? 0).'|'.($r->dependency_id ?? 0).'|'.($r->affiliation_id ?? 0);
                    $activeRoles[$r->participant_id][$key] = $r->id;
                });
        }

        // ── Sincronizar participantes existentes ──────────────────────────
        $rolesActivated = 0;
        $rolesCreated = 0;
        $rolesSkippedConflict = 0;
        $updatedParticipants = 0;

        foreach ($excelRolesForExisting as $pid => $wantedRoles) {
            $currentRoleKeys = $activeRoles[$pid] ?? [];
            $wantedKeys = array_keys($wantedRoles);
            $currentKeys = array_keys($currentRoleKeys);

            $toActivate = array_diff($wantedKeys, $currentKeys);

            $changed = false;

            foreach ($toActivate as $roleKey) {
                $role = $wantedRoles[$roleKey];

                $updated = DB::table('participant_roles')
                    ->where('participant_id', $pid)
                    ->where('participant_type_id', $role['participant_type_id'])
                    ->where(fn ($q) => $role['program_id']
                        ? $q->where('program_id', $role['program_id'])
                        : $q->whereNull('program_id'))
                    ->where(fn ($q) => $role['dependency_id']
                        ? $q->where('dependency_id', $role['dependency_id'])
                        : $q->whereNull('dependency_id'))
                    ->where(fn ($q) => $role['affiliation_id']
                        ? $q->where('affiliation_id', $role['affiliation_id'])
                        : $q->whereNull('affiliation_id'))
                    ->where('is_active', 0)
                    ->update(['is_active' => 1, 'updated_at' => $now]);

                if ($updated) {
                    $rolesActivated++;
                } else {
                    DB::table('participant_roles')->insert([
                        'participant_id' => $pid,
                        'participant_type_id' => $role['participant_type_id'],
                        'program_id' => $role['program_id'],
                        'dependency_id' => $role['dependency_id'],
                        'affiliation_id' => $role['affiliation_id'],
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $rolesCreated++;
                }
                $changed = true;
            }

            if ($changed) {
                $updatedParticipants++;
            }
        }

        return [
            'saved' => $saved,
            'updated_participants' => $updatedParticipants,
            'roles_activated' => $rolesActivated,
            'roles_created' => $rolesCreated,
            'roles_skipped_conflict' => $rolesSkippedConflict,
        ];
    }

    /**
     * Historial de lotes de importación. Permite volver a un lote ya procesado
     * (p. ej. para descargar sus filas omitidas).
     */
    public function batches()
    {
        $batches = ImportBatch::with('user')
            ->latest()
            ->paginate(20);

        return view('administration.participants.batches', compact('batches'));
    }

    /**
     * Pantalla de revisión de un lote en staging.
     */
    public function review(Request $request, ImportBatch $batch)
    {
        $estado = $request->query('estado');
        $estado = in_array($estado, ['nuevo', 'actualiza', 'omitido'], true) ? $estado : null;

        $rows = $batch->stagedParticipants()
            ->when($estado, fn ($q) => $q->where('status', $estado))
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $typeNames = ParticipantType::pluck('name', 'id');
        $programNames = Program::pluck('name', 'id');
        $dependencyNames = Dependency::pluck('name', 'id');
        $affiliationNames = Affiliation::pluck('name', 'id');

        return view('administration.participants.review', compact(
            'batch', 'rows', 'estado',
            'typeNames', 'programNames', 'dependencyNames', 'affiliationNames',
        ));
    }

    /**
     * Aprueba un lote: aplica el plan a las tablas principales.
     */
    public function approve(Request $request, ImportBatch $batch)
    {
        if ($batch->status !== 'en_revision') {
            return redirect()->route('participants-import.index')
                ->with('error', 'Este lote ya fue procesado.');
        }

        // Re-autenticación: el admin confirma con su contraseña antes de aplicar
        // el lote a las tablas principales.
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Ingresa tu contraseña para confirmar la importación.',
            'password.current_password' => 'La contraseña no es correcta. Inténtalo de nuevo.',
        ]);

        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        DB::connection()->disableQueryLog();

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newRoles = [];
        $excelRolesForExisting = [];

        $rebuildRoles = function ($rolesJson): array {
            $roles = [];
            foreach (($rolesJson ?? []) as $r) {
                $key = ($r['participant_type_id'] ?? 0).'|'.($r['program_id'] ?? 0).'|'.($r['dependency_id'] ?? 0).'|'.($r['affiliation_id'] ?? 0);
                $roles[$key] = [
                    'participant_type_id' => $r['participant_type_id'] ?? null,
                    'program_id' => $r['program_id'] ?? null,
                    'dependency_id' => $r['dependency_id'] ?? null,
                    'affiliation_id' => $r['affiliation_id'] ?? null,
                ];
            }

            return $roles;
        };

        $batch->stagedParticipants()->where('status', 'nuevo')->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$newParticipants, &$newRoles, $now, $rebuildRoles) {
                foreach ($chunk as $s) {
                    if (! $s->document) {
                        continue;
                    }
                    $newParticipants[$s->document] = [
                        'document' => $s->document,
                        'student_code' => null,
                        'first_name' => $s->first_name,
                        'last_name' => $s->last_name,
                        'email' => $s->email ?: null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $newRoles[$s->document] = $rebuildRoles($s->roles);
                }
            });

        $batch->stagedParticipants()->where('status', 'actualiza')->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$excelRolesForExisting, $rebuildRoles) {
                foreach ($chunk as $s) {
                    if (! $s->existing_participant_id) {
                        continue;
                    }
                    $excelRolesForExisting[$s->existing_participant_id] = $rebuildRoles($s->roles);
                }
            });

        $result = DB::transaction(fn () => $this->commitPlan($newParticipants, $newRoles, $excelRolesForExisting));

        $batch->update(['status' => 'aprobado', 'applied_at' => now()]);

        // Compatibilidad con el botón "Descargar omitidos" del banner del índice.
        $skippedRaws = $batch->stagedParticipants()->where('status', 'omitido')
            ->get()->map(fn ($s) => $s->raw ?? [])->filter()->values()->all();
        session(['import_skipped' => $skippedRaws]);

        ActivityLogService::log('importar', 'participantes', "Aprobó e importó el lote #{$batch->id}", $batch, $result + ['skipped' => $batch->skipped_count]);

        return redirect()->route('participants-import.index')
            ->with('active_tab', 'list')
            ->with('import_result', $result + ['skipped' => $batch->skipped_count]);
    }

    /**
     * Rechaza un lote sin tocar las tablas principales.
     */
    public function reject(ImportBatch $batch)
    {
        if ($batch->status !== 'en_revision') {
            return redirect()->route('participants-import.index')
                ->with('error', 'Este lote ya fue procesado.');
        }

        $batch->update(['status' => 'rechazado']);

        ActivityLogService::log('importar', 'participantes', "Rechazó el lote de importación #{$batch->id}");

        return redirect()->route('participants-import.index')
            ->with('success', 'Lote rechazado. No se guardó ningún registro.');
    }

    /**
     * Descarga en Excel las filas omitidas de un lote.
     */
    public function downloadBatchSkipped(ImportBatch $batch)
    {
        $rows = $batch->stagedParticipants()->where('status', 'omitido')
            ->get()->map(fn ($s) => $s->raw ?? [])->filter()->values()->all();

        if (empty($rows)) {
            return redirect()->route('participants-import.review', $batch)
                ->with('error', 'No hay filas omitidas para descargar en este lote.');
        }

        return Excel::download(
            new \App\Exports\SkippedParticipantsExport($rows),
            'omitidos_lote_'.$batch->id.'.xlsx'
        );
    }

    public function downloadSkipped()
    {
        $skipped = session('import_skipped', []);

        if (empty($skipped)) {
            return redirect()->route('participants-import.index')
                ->with('error', 'No hay datos omitidos disponibles para descargar.');
        }

        return Excel::download(
            new \App\Exports\SkippedParticipantsExport($skipped),
            'participantes_omitidos_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function store(Request $request, CampusScopeService $campusScope)
    {
        $validTypes = ParticipantType::pluck('name')->toArray();

        $request->validate([
            'document' => 'required|string|max:20|unique:participants,document',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:participants,email',
            'role' => ['required', 'string', Rule::in($validTypes)],
            'student_code' => 'nullable|string|max:20|unique:participants,student_code',
            'affiliation_id' => 'nullable|exists:affiliations,id',
            'program_id' => 'nullable|exists:programs,id',
            'dependency_id' => 'nullable|exists:dependencies,id',
            'organization_name' => 'nullable|string|max:150',
            'organization_id' => 'nullable|exists:organizations,id',
        ], [
            'document.required' => 'El documento es obligatorio.',
            'document.unique' => 'Ya existe un participante con ese documento.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Ya existe un participante con ese correo.',
            'role.required' => 'El estamento es obligatorio.',
            'role.in' => 'El estamento seleccionado no es válido.',
            'student_code.unique' => 'Ya existe un participante con ese código estudiantil.',
        ]);

        $program = $request->program_id ? Program::findOrFail($request->integer('program_id')) : null;
        if ($program) {
            $campusScope->authorizeResource($request->user(), $program);
        }

        DB::beginTransaction();

        try {
            $participant = Participant::create([
                'document' => trim($request->document),
                'first_name' => mb_convert_case(mb_strtolower(trim($request->first_name), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'last_name' => mb_convert_case(mb_strtolower(trim($request->last_name), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'email' => $request->email ?: null,
                'student_code' => $request->student_code ?: null,
            ]);

            $type = ParticipantType::where('name', $request->role)->first();

            if ($type) {
                // Resolver organization_id para Comunidad Externa
                $organizationId = null;
                if (mb_strtolower(trim($request->role), 'UTF-8') === 'comunidad externa') {
                    $organizationId = $request->organization_id ?: null;
                    if (! $organizationId && ! empty($request->organization_name)) {
                        $normalizedInput = trim($request->organization_name);
                        $org = \App\Models\Organization::whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedInput, 'UTF-8')])->first()
                            ?? \App\Models\Organization::create(['name' => $normalizedInput]);
                        $organizationId = $org->id;
                    }
                }

                \App\Models\ParticipantRole::create([
                    'participant_id' => $participant->id,
                    'participant_type_id' => $type->id,
                    'program_id' => $request->program_id ?: null,
                    'dependency_id' => $request->dependency_id ?: null,
                    'affiliation_id' => $request->affiliation_id ?: null,
                    'organization_id' => $organizationId,
                    'is_active' => 1,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $fullName = trim($participant->first_name.' '.$participant->last_name);
        ActivityLogService::log('crear', 'participantes', "Creó el participante '{$fullName}' (Doc: {$participant->document})", $participant);

        return redirect()->route('participants-import.index')
            ->with('success', 'Participante registrado exitosamente.');
    }

    private function uniqueCatalogId(array $ids): ?int
    {
        $ids = array_keys($ids);

        return count($ids) === 1 ? (int) $ids[0] : null;
    }

    private function defaultCampusIdForImport(Request $request, CampusScopeService $campusScope): ?int
    {
        $user = $request->user();

        if ($user?->isSuperadmin()) {
            return null;
        }

        $campusId = $campusScope->activeCampusId($user);

        if (! $campusId) {
            $message = 'Tu usuario no tiene una sede asignada para importar participantes.';

            throw \Illuminate\Validation\ValidationException::withMessages(['excel_file' => $message]);
        }

        return $campusId ? (int) $campusId : null;
    }

    private function resolveImportCampusId(Request $request, ?int $defaultCampusId, ?int $suffixCampusId): ?int
    {
        $campusId = $defaultCampusId ?? $suffixCampusId;

        if (! $campusId) {
            return null;
        }

        $user = $request->user();

        if (! $user?->isSuperadmin() && (int) $user?->campus_id !== (int) $campusId) {
            return null;
        }

        return (int) $campusId;
    }

    /**
     * Lee un CSV de forma nativa (rápido) devolviendo filas como arrays
     * numéricos, equivalente a lo que entrega `Excel::toArray()[0]`.
     * - Quita BOM UTF-8.
     * - Normaliza la codificación a UTF-8 (Windows-1252 es común en Excel Windows).
     * - Detecta el separador (',' / ';' / tabulador).
     * - Usa fgetcsv para respetar comillas y saltos de línea dentro de campos.
     */
    private function readCsvRows(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || $content === '') {
            return [];
        }

        // Quitar BOM UTF-8 si existe.
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Si no es UTF-8 válido, asumir Windows-1252 (Excel en Windows).
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

    /**
     * Detecta el separador del CSV mirando la primera línea.
     */
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

        // Reparar mojibake frecuente en archivos Excel exportados con codificacion mixta.
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

            // Acepta coincidencias muy cercanas (errores de codificacion/letras puntuales).
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
