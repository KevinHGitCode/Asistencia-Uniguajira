<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
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

    public function index()
    {
        $programs     = Program::orderBy('name')->get(['id', 'name']);
        $dependencies = Dependency::orderBy('name')->get(['id', 'name']);
        $affiliations = Affiliation::orderBy('name')->get(['id', 'name']);
        $estamentos   = ParticipantType::orderBy('name')->get(['id', 'name']);

        return view('administration.participants.index', compact('programs', 'dependencies', 'affiliations', 'estamentos'));
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\ParticipantTemplateExport(),
            'plantilla_participantes.xlsx'
        );
    }

    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
            'excel_file.max'      => 'El archivo no debe superar los 20 MB.',
            'excel_file.uploaded' => 'No se pudo subir el archivo Excel. Verifica el tamano del archivo y vuelve a intentarlo.',
        ], [
            'excel_file' => 'archivo Excel',
        ]);

        $sheets  = Excel::toArray([], $request->file('excel_file'));
        $allRows = $sheets[0] ?? [];

        if (empty($allRows)) {
            return back()->withErrors(['excel_file' => 'El archivo está vacío.']);
        }

        // ── Leer y validar cabeceras ──────────────────────────────────────
        $headerRow = array_values((array) $allRows[0]);
        $headers   = array_map(fn ($h) => trim((string) ($h ?? '')), $headerRow);

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
                    . implode(', ', array_map(fn ($c) => "«{$c}»", $missing))
                    . '. Descarga la plantilla oficial y vuelve a intentarlo.',
            ]);
        }

        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        array_shift($allRows);
        $rows = $allRows;

        // ── Cachés de lookup ──────────────────────────────────────────────
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

        $existingDocToId = DB::table('participants')->pluck('id', 'document')->toArray();
        $existingEmails  = DB::table('participants')
            ->whereNotNull('email')->pluck('email')->flip()->toArray();

        // ── Roles activos actuales de participantes existentes ─────────────
        $activeRoles = [];

        if (! empty($existingDocToId)) {
            DB::table('participant_roles')
                ->whereIn('participant_id', array_values($existingDocToId))
                ->where('is_active', 1)
                ->get(['id', 'participant_id', 'participant_type_id', 'program_id', 'dependency_id', 'affiliation_id'])
                ->each(function ($r) use (&$activeRoles) {
                    $key = ($r->participant_type_id ?? 0) . '|' . ($r->program_id ?? 0) . '|' . ($r->dependency_id ?? 0) . '|' . ($r->affiliation_id ?? 0);
                    $activeRoles[$r->participant_id][$key] = $r->id;
                });
        }

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newRoles        = [];

        $excelRolesForExisting = [];

        $skipped = [];

        // ── Primera pasada: clasificar cada fila ──────────────────────────
        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $document        = trim((string) ($get($rawValues, 'Documento') ?? ''));
            $firstNameRaw    = self::normalizeExcelText($get($rawValues, 'Nombres'));
            $lastNameRaw     = self::normalizeExcelText($get($rawValues, 'Apellidos'));
            $roleName        = self::normalizeExcelText($get($rawValues, 'Tipo de Estamento'));
            $emailRaw        = self::normalizeExcelText($get($rawValues, 'Correo'));
            $programName     = self::normalizeExcelText($get($rawValues, 'Programa o Dependencia'));
            $programTypeRaw  = self::normalizeExcelText($get($rawValues, 'Tipo_progama'));
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

            // ── Validar tipo de estamento ─────────────────────────────────
            $roleKey  = ProgramController::comparisonKey($roleName);
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

            // ── Determinar si es programa o dependencia ───────────────────
            $isProgramType = in_array(
                mb_strtolower($programTypeRaw, 'UTF-8'),
                self::PROGRAM_TYPES,
                true
            );

            $programId    = null;
            $dependencyId = null;

            if ($programName !== '') {
                $rawProgramName = $programName;
                $nameKey        = ProgramController::comparisonKey($rawProgramName);

                if ($isProgramType) {
                    $programId = $programByNameHash[$nameKey]
                        ?? $this->findClosestProgramId($rawProgramName, $programByNameHash);

                    if (! $programId) {
                        $skipped[] = $this->skippedRow(
                            $rawValues, $headers,
                            "Programa no encontrado: \"{$rawProgramName}\""
                        );
                        continue;
                    }
                } else {
                    if (! isset($dependencyHash[$nameKey])) {
                        $cleanName = preg_replace('/\s+/u', ' ', $programName);
                        $dep = Dependency::create(['name' => $cleanName]);
                        $dependencyHash[$nameKey] = $dep->id;
                    }
                    $dependencyId = $dependencyHash[$nameKey];
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
            $compositeKey = ($typeId ?? 0) . '|' . ($programId ?? 0) . '|' . ($dependencyId ?? 0) . '|' . ($affiliationId ?? 0);
            $roleData = [
                'participant_type_id' => $typeId,
                'program_id'          => $programId,
                'dependency_id'       => $dependencyId,
                'affiliation_id'      => $affiliationId,
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
                'document'     => $document,
                'student_code' => null,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => $email ?: null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $newRoles[$document] = [$compositeKey => $roleData];

            if ($email) {
                $existingEmails[$email] = true;
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Insertar nuevos participantes ─────────────────────────────────
        // ══════════════════════════════════════════════════════════════════
        $saved   = 0;
        $batch   = [];
        $docKeys = [];

        foreach ($newParticipants as $doc => $data) {
            $batch[]   = $data;
            $docKeys[] = $doc;
            if (count($batch) === self::BATCH_SIZE) {
                DB::table('participants')->insert($batch);
                $saved += self::BATCH_SIZE;
                $batch  = [];
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
                        'participant_id'      => $pid,
                        'participant_type_id' => $role['participant_type_id'],
                        'program_id'          => $role['program_id'],
                        'dependency_id'       => $role['dependency_id'],
                        'affiliation_id'      => $role['affiliation_id'],
                        'is_active'           => 1,
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

        // ══════════════════════════════════════════════════════════════════
        // ── Sincronizar participantes existentes ──────────────────────────
        // ══════════════════════════════════════════════════════════════════
        $rolesActivated      = 0;
        $rolesDeactivated    = 0;
        $rolesCreated        = 0;
        $updatedParticipants = 0;

        foreach ($excelRolesForExisting as $pid => $wantedRoles) {
            $currentRoleKeys = $activeRoles[$pid] ?? [];
            $wantedKeys      = array_keys($wantedRoles);
            $currentKeys     = array_keys($currentRoleKeys);

            $toDeactivate = array_diff($currentKeys, $wantedKeys);
            $toActivate   = array_diff($wantedKeys, $currentKeys);

            $changed = false;

            if (! empty($toDeactivate)) {
                $idsToDeactivate = array_map(fn ($k) => $currentRoleKeys[$k], $toDeactivate);
                DB::table('participant_roles')
                    ->whereIn('id', $idsToDeactivate)
                    ->update(['is_active' => 0, 'updated_at' => $now]);
                $rolesDeactivated += count($toDeactivate);
                $changed = true;
            }

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
                        'participant_id'      => $pid,
                        'participant_type_id' => $role['participant_type_id'],
                        'program_id'          => $role['program_id'],
                        'dependency_id'       => $role['dependency_id'],
                        'affiliation_id'      => $role['affiliation_id'],
                        'is_active'           => 1,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                    $rolesCreated++;
                }
                $changed = true;
            }

            if ($changed) {
                $updatedParticipants++;
            }
        }

        session(['import_skipped' => $skipped]);

        return redirect()->route('participants-import.index')
            ->with('import_result', [
                'saved'                => $saved,
                'updated_participants' => $updatedParticipants,
                'roles_activated'      => $rolesActivated,
                'roles_deactivated'    => $rolesDeactivated,
                'roles_created'        => $rolesCreated,
                'skipped'              => count($skipped),
            ]);
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
            'participantes_omitidos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function store(Request $request)
    {
        $validTypes = ParticipantType::pluck('name')->toArray();

        $request->validate([
            'document'       => 'required|string|max:20|unique:participants,document',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'nullable|email|max:255|unique:participants,email',
            'role'           => ['required', 'string', Rule::in($validTypes)],
            'student_code'   => 'nullable|string|max:20|unique:participants,student_code',
            'affiliation_id' => 'nullable|exists:affiliations,id',
            'program_id'     => 'nullable|exists:programs,id',
            'dependency_id'  => 'nullable|exists:dependencies,id',
            'sexo'           => 'nullable|in:Masculino,Femenino,No binario',
            'priority_group' => 'nullable|string|max:150',
        ], [
            'document.required'   => 'El documento es obligatorio.',
            'document.unique'     => 'Ya existe un participante con ese documento.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required'  => 'El apellido es obligatorio.',
            'email.email'         => 'El correo no tiene un formato válido.',
            'email.unique'        => 'Ya existe un participante con ese correo.',
            'role.required'       => 'El estamento es obligatorio.',
            'role.in'             => 'El estamento seleccionado no es válido.',
            'student_code.unique' => 'Ya existe un participante con ese código estudiantil.',
        ]);

        $participant = Participant::create([
            'document'       => trim($request->document),
            'first_name'     => mb_convert_case(mb_strtolower(trim($request->first_name), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
            'last_name'      => mb_convert_case(mb_strtolower(trim($request->last_name), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
            'email'          => $request->email ?: null,
            'student_code'   => $request->student_code ?: null,
            'gender'         => $request->sexo ?: null,
            'priority_group' => $request->priority_group ?: null,
        ]);

        $type = ParticipantType::where('name', $request->role)->first();

        if ($type) {
            DB::table('participant_roles')->insert([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'program_id'          => $request->program_id ?: null,
                'dependency_id'       => $request->dependency_id ?: null,
                'affiliation_id'      => $request->affiliation_id ?: null,
                'is_active'           => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        return redirect()->route('participants-import.index')
            ->with('success', 'Participante registrado exitosamente.');
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
