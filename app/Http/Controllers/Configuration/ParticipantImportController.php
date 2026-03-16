<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
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

    public function index()
    {
        $programs         = Program::orderBy('name')->get(['id', 'name', 'campus']);
        $affiliations     = Affiliation::orderBy('name')->get(['id', 'name']);
        $estamentos       = ParticipantType::orderBy('name')->get(['id', 'name']);

        return view('administration.participants.index', compact('programs', 'affiliations', 'estamentos'));
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
        foreach (Program::all(['id', 'name']) as $program) {
            $nameKey = ProgramController::comparisonKey($program->name);
            if (! isset($programByNameHash[$nameKey])) {
                $programByNameHash[$nameKey] = $program->id;
            }
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $aff) {
            $affiliationHash[ProgramController::comparisonKey($aff->name)] = $aff->id;
        }

        $typeHash = [];
        foreach (ParticipantType::all(['id', 'name']) as $type) {
            $typeHash[ProgramController::comparisonKey($type->name)] = ['id' => $type->id, 'name' => $type->name];
        }

        $existingDocToId = DB::table('participants')->pluck('id', 'document')->toArray();
        $existingEmails  = DB::table('participants')
            ->whereNotNull('email')->pluck('email')->flip()->toArray();

        // Relaciones ACTIVAS actuales de cada participante existente
        $activeTypes = [];
        $activePrograms = [];
        $activeAffiliations = [];

        if (! empty($existingDocToId)) {
            $pids = array_values($existingDocToId);

            DB::table('participant_type_participant')
                ->whereIn('participant_id', $pids)
                ->where('is_active', 1)
                ->get(['participant_id', 'participant_type_id'])
                ->each(function ($r) use (&$activeTypes) {
                    $activeTypes[$r->participant_id][$r->participant_type_id] = true;
                });

            DB::table('participant_program')
                ->whereIn('participant_id', $pids)
                ->where('is_active', 1)
                ->get(['participant_id', 'program_id'])
                ->each(function ($r) use (&$activePrograms) {
                    $activePrograms[$r->participant_id][$r->program_id] = true;
                });

            DB::table('affiliation_participant')
                ->whereIn('participant_id', $pids)
                ->where('is_active', 1)
                ->get(['participant_id', 'affiliation_id'])
                ->each(function ($r) use (&$activeAffiliations) {
                    $activeAffiliations[$r->participant_id][$r->affiliation_id] = true;
                });
        }

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newPrograms     = [];
        $newTypes        = [];
        $newAffiliations = [];

        // Para existentes: acumula el SET COMPLETO que viene en el Excel
        // [pid] => ['types' => [id,...], 'programs' => [id,...], 'affiliations' => [id,...]]
        $excelSetForExisting = [];

        $skipped = [];

        // ── Primera pasada: clasificar cada fila ──────────────────────────
        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $document  = trim((string) ($get($rawValues, 'Documento') ?? ''));
            $firstName = mb_convert_case(mb_strtolower(trim((string) ($get($rawValues, 'Nombres') ?? '')), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $lastName  = mb_convert_case(mb_strtolower(trim((string) ($get($rawValues, 'Apellidos') ?? '')), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $roleName  = trim((string) ($get($rawValues, 'Tipo de Estamento') ?? ''));
            $emailRaw  = $get($rawValues, 'Correo');
            $email     = $emailRaw ? mb_strtolower(trim((string) $emailRaw), 'UTF-8') : null;
            $programName     = $get($rawValues, 'Programa o Dependencia');
            $affiliationType = $get($rawValues, 'Vinculacion');

            if ($document === '') {
                $skipped[] = $this->skippedRow($rawValues, $headers, 'Documento vacío');
                continue;
            }

            // Validar tipo
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

            // Resolver program_id
            $programId = null;
            if (! empty($programName)) {
                $rawProgramName = trim(explode(' - ', (string) $programName, 2)[0]);
                $nameKey        = ProgramController::comparisonKey($rawProgramName);
                if (! isset($programByNameHash[$nameKey])) {
                    $skipped[] = $this->skippedRow(
                        $rawValues, $headers,
                        "Programa no encontrado: \"{$rawProgramName}\""
                    );
                    continue;
                }
                $programId = $programByNameHash[$nameKey];
            }

            // Resolver affiliation_id
            $affiliationId = null;
            if (! empty($affiliationType) && $affiliationType !== '0' && $affiliationType !== 0) {
                $affKey = ProgramController::comparisonKey($affiliationType);
                if (! isset($affiliationHash[$affKey])) {
                    $aff                      = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── 1) Mismo doc ya visto en este archivo (nuevo) ─────────────
            if (isset($newParticipants[$document])) {
                if ($programId && ! in_array($programId, $newPrograms[$document] ?? [], true)) {
                    $newPrograms[$document][] = $programId;
                }
                if ($typeId && ! in_array($typeId, $newTypes[$document] ?? [], true)) {
                    $newTypes[$document][] = $typeId;
                }
                if ($affiliationId && ! in_array($affiliationId, $newAffiliations[$document] ?? [], true)) {
                    $newAffiliations[$document][] = $affiliationId;
                }
                continue;
            }

            // ── 2) Doc ya existe en BD ────────────────────────────────────
            if (isset($existingDocToId[$document])) {
                $pid = $existingDocToId[$document];

                // Mismo doc ya visto en este archivo (existente en BD)
                if (isset($excelSetForExisting[$pid])) {
                    if ($typeId && ! in_array($typeId, $excelSetForExisting[$pid]['types'], true)) {
                        $excelSetForExisting[$pid]['types'][] = $typeId;
                    }
                    if ($programId && ! in_array($programId, $excelSetForExisting[$pid]['programs'], true)) {
                        $excelSetForExisting[$pid]['programs'][] = $programId;
                    }
                    if ($affiliationId && ! in_array($affiliationId, $excelSetForExisting[$pid]['affiliations'], true)) {
                        $excelSetForExisting[$pid]['affiliations'][] = $affiliationId;
                    }
                } else {
                    $excelSetForExisting[$pid] = [
                        'types'        => $typeId ? [$typeId] : [],
                        'programs'     => $programId ? [$programId] : [],
                        'affiliations' => $affiliationId ? [$affiliationId] : [],
                    ];
                }
                continue;
            }

            // ── 3) Email duplicado ────────────────────────────────────────
            if ($email !== null && isset($existingEmails[$email])) {
                $skipped[] = $this->skippedRow($rawValues, $headers, "Correo duplicado ({$email})");
                continue;
            }

            // ── 4) Nuevo participante ─────────────────────────────────────
            $newParticipants[$document] = [
                'document'       => $document,
                'student_code'   => null,
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'email'          => $email ?: null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
            $newPrograms[$document]     = $programId ? [$programId] : [];
            $newTypes[$document]        = [$typeId];
            $newAffiliations[$document] = $affiliationId ? [$affiliationId] : [];

            if ($email) {
                $existingEmails[$email] = true;
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Insertar nuevos participantes en lotes ────────────────────────
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

        // ── Pivot para nuevos participantes ───────────────────────────────
        if (! empty($docKeys)) {
            $docToId = DB::table('participants')
                ->whereIn('document', $docKeys)
                ->pluck('id', 'document')
                ->toArray();

            $programBatch     = [];
            $typeBatch        = [];
            $affiliationBatch = [];

            foreach ($docKeys as $doc) {
                $pid = $docToId[$doc] ?? null;
                if (! $pid) {
                    continue;
                }

                foreach (($newPrograms[$doc] ?? []) as $programId) {
                    $programBatch[] = ['participant_id' => $pid, 'program_id' => $programId, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
                    if (count($programBatch) === self::BATCH_SIZE) {
                        DB::table('participant_program')->insert($programBatch);
                        $programBatch = [];
                    }
                }

                foreach (($newTypes[$doc] ?? []) as $typeId) {
                    $typeBatch[] = ['participant_id' => $pid, 'participant_type_id' => $typeId, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
                    if (count($typeBatch) === self::BATCH_SIZE) {
                        DB::table('participant_type_participant')->insert($typeBatch);
                        $typeBatch = [];
                    }
                }

                foreach (($newAffiliations[$doc] ?? []) as $affiliationId) {
                    $affiliationBatch[] = ['participant_id' => $pid, 'affiliation_id' => $affiliationId, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
                    if (count($affiliationBatch) === self::BATCH_SIZE) {
                        DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
                        $affiliationBatch = [];
                    }
                }
            }
            if (! empty($programBatch)) {
                DB::table('participant_program')->insert($programBatch);
            }
            if (! empty($typeBatch)) {
                DB::table('participant_type_participant')->insert($typeBatch);
            }
            if (! empty($affiliationBatch)) {
                DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // ── Sincronizar participantes existentes ──────────────────────────
        // Para cada participante que ya estaba en BD y aparece en el Excel:
        //   - Desactivar relaciones activas que NO vienen en el Excel
        //   - Activar relaciones inactivas que SÍ vienen en el Excel
        //   - Crear relaciones que no existían en ninguna forma
        // ══════════════════════════════════════════════════════════════════
        $typesActivated        = 0;
        $typesDeactivated      = 0;
        $programsActivated     = 0;
        $programsDeactivated   = 0;
        $affiliationsActivated   = 0;
        $affiliationsDeactivated = 0;
        $updatedParticipants   = 0;

        foreach ($excelSetForExisting as $pid => $sets) {
            $changed = false;

            // ── TIPOS ─────────────────────────────────────────────────────
            $wantedTypes  = $sets['types'];
            $currentTypes = array_keys($activeTypes[$pid] ?? []);

            $toDeactivate = array_diff($currentTypes, $wantedTypes);
            $toActivate   = array_diff($wantedTypes, $currentTypes);

            if (! empty($toDeactivate)) {
                DB::table('participant_type_participant')
                    ->where('participant_id', $pid)
                    ->where('is_active', 1)
                    ->whereIn('participant_type_id', $toDeactivate)
                    ->update(['is_active' => 0, 'updated_at' => $now]);
                $typesDeactivated += count($toDeactivate);
                $changed = true;
            }

            foreach ($toActivate as $typeId) {
                DB::table('participant_type_participant')->updateOrInsert(
                    ['participant_id' => $pid, 'participant_type_id' => $typeId],
                    ['is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
                );
                $typesActivated++;
                $changed = true;
            }

            // ── PROGRAMAS ─────────────────────────────────────────────────
            $wantedPrograms  = $sets['programs'];
            $currentPrograms = array_keys($activePrograms[$pid] ?? []);

            $toDeactivate = array_diff($currentPrograms, $wantedPrograms);
            $toActivate   = array_diff($wantedPrograms, $currentPrograms);

            if (! empty($toDeactivate)) {
                DB::table('participant_program')
                    ->where('participant_id', $pid)
                    ->where('is_active', 1)
                    ->whereIn('program_id', $toDeactivate)
                    ->update(['is_active' => 0, 'updated_at' => $now]);
                $programsDeactivated += count($toDeactivate);
                $changed = true;
            }

            foreach ($toActivate as $programId) {
                DB::table('participant_program')->updateOrInsert(
                    ['participant_id' => $pid, 'program_id' => $programId],
                    ['is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
                );
                $programsActivated++;
                $changed = true;
            }

            // ── VINCULACIONES ─────────────────────────────────────────────
            $wantedAffiliations  = $sets['affiliations'];
            $currentAffiliations = array_keys($activeAffiliations[$pid] ?? []);

            $toDeactivate = array_diff($currentAffiliations, $wantedAffiliations);
            $toActivate   = array_diff($wantedAffiliations, $currentAffiliations);

            if (! empty($toDeactivate)) {
                DB::table('affiliation_participant')
                    ->where('participant_id', $pid)
                    ->where('is_active', 1)
                    ->whereIn('affiliation_id', $toDeactivate)
                    ->update(['is_active' => 0, 'updated_at' => $now]);
                $affiliationsDeactivated += count($toDeactivate);
                $changed = true;
            }

            foreach ($toActivate as $affiliationId) {
                DB::table('affiliation_participant')->updateOrInsert(
                    ['participant_id' => $pid, 'affiliation_id' => $affiliationId],
                    ['is_active' => 1, 'created_at' => $now, 'updated_at' => $now]
                );
                $affiliationsActivated++;
                $changed = true;
            }

            if ($changed) {
                $updatedParticipants++;
            }
        }

        session(['import_skipped' => $skipped]);

        return redirect()->route('participants-import.index')
            ->with('import_result', [
                'saved'                    => $saved,
                'updated_participants'     => $updatedParticipants,
                'types_activated'          => $typesActivated,
                'types_deactivated'        => $typesDeactivated,
                'programs_activated'       => $programsActivated,
                'programs_deactivated'     => $programsDeactivated,
                'affiliations_activated'   => $affiliationsActivated,
                'affiliations_deactivated' => $affiliationsDeactivated,
                'skipped'                  => count($skipped),
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

        if ($request->role === 'Docente' && $request->affiliation_id) {
            $participant->affiliations()->attach($request->affiliation_id);
        }

        $type = ParticipantType::where('name', $request->role)->first();
        if ($type) {
            $participant->types()->attach($type->id);
        }

        if ($request->program_id && in_array($request->role, ['Estudiante', 'Graduado'])) {
            $participant->programs()->attach($request->program_id);
        }

        return redirect()->route('participants-import.index')
            ->with('success', 'Participante registrado exitosamente.');
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