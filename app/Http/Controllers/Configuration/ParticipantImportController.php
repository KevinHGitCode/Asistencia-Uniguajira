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

    private const COLUMN_MAP = [
        'Documento'              => 'document',
        'Nombres'                => 'first_name',
        'Apellidos'              => 'last_name',
        'Tipo de Estamento'      => 'role',
        'Correo'                 => 'email',
        'Programa o Dependencia' => 'program',
        'Tipo_progama'           => 'program_type',
        'Vinculacion'            => 'affiliation',
        // 'Codigo de Estudiante' => 'student_code',
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
        $programHash     = [];
        $programByNameHash = []; // fallback: name only → first matching program id
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

        // Tipos válidos: lowercase name → [id, canonical name]
        $typeHash = [];
        foreach (ParticipantType::all(['id', 'name']) as $type) {
            $typeHash[strtolower($type->name)] = ['id' => $type->id, 'name' => $type->name];
        }

        $existingDocToId = DB::table('participants')->pluck('id', 'document')->toArray();
        $existingEmails  = DB::table('participants')
            ->whereNotNull('email')->pluck('email')->flip()->toArray();

        // Programas ya asignados: [participant_id][program_id] = true
        $existingPivot = [];
        if (! empty($existingDocToId)) {
            DB::table('participant_program')
                ->whereIn('participant_id', array_values($existingDocToId))
                ->get(['participant_id', 'program_id'])
                ->each(fn ($r) => $existingPivot[$r->participant_id][$r->program_id] = true);
        }

        // Tipos ya asignados: [participant_id][participant_type_id] = true
        $existingTypePivot = [];
        if (! empty($existingDocToId)) {
            DB::table('participant_type_participant')
                ->whereIn('participant_id', array_values($existingDocToId))
                ->get(['participant_id', 'participant_type_id'])
                ->each(fn ($r) => $existingTypePivot[$r->participant_id][$r->participant_type_id] = true);
        }

        // Affiliations ya asignadas: [participant_id][affiliation_id] = true
        $existingAffiliationPivot = [];
        if (! empty($existingDocToId)) {
            DB::table('affiliation_participant')
                ->whereIn('participant_id', array_values($existingDocToId))
                ->get(['participant_id', 'affiliation_id'])
                ->each(fn ($r) => $existingAffiliationPivot[$r->participant_id][$r->affiliation_id] = true);
        }

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newPrograms     = [];
        $newTypes        = [];
        $newAffiliations = [];
        $existingUpdates = [];
        $skipped         = [];

        // ── Primera pasada: clasificar cada fila ──────────────────────────
        foreach ($rows as $row) {
            $rawValues = array_values((array) $row);
            if (empty(array_filter($rawValues, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $document  = trim((string) ($get($rawValues, 'Documento') ?? ''));
            $firstName = ucwords(strtolower(trim((string) ($get($rawValues, 'Nombres') ?? ''))));
            $lastName  = ucwords(strtolower(trim((string) ($get($rawValues, 'Apellidos') ?? ''))));
            $roleName  = trim((string) ($get($rawValues, 'Tipo de Estamento') ?? ''));
            $emailRaw  = $get($rawValues, 'Correo');
            $email     = $emailRaw ? strtolower(trim((string) $emailRaw)) : null;
            $programName     = $get($rawValues, 'Programa o Dependencia');
            $affiliationType = $get($rawValues, 'Vinculacion');

            if ($document === '') {
                $skipped[] = $this->skippedRow($rawValues, $headers, 'Documento vacío');
                continue;
            }

            // Validar tipo (case-insensitive)
            $roleKey  = strtolower($roleName);
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
            $roleName = $typeData['name'];  // nombre canónico de la BD
            $typeId   = $typeData['id'];

            // Resolver program_id
            $programId = null;
            if (! empty($programName)) {
                $parts      = explode(' - ', (string) $programName, 2);
                $programKey = strtolower(trim($parts[0])) . '|' . strtolower(trim($parts[1] ?? ''));
                $programId  = $programHash[$programKey]
                    ?? $programByNameHash[strtolower(trim($parts[0]))]
                    ?? null;
            }

            // Resolver affiliation_id
            $affiliationId = null;
            if (! empty($affiliationType) && $affiliationType !== '0' && $affiliationType !== 0) {
                $affKey = strtolower(trim((string) $affiliationType));
                if (! isset($affiliationHash[$affKey])) {
                    $aff                      = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── Doc ya existe en BD ───────────────────────────────────────
            if (isset($existingDocToId[$document])) {
                $pid = $existingDocToId[$document];

                $hasNewProgram     = $programId && ! isset($existingPivot[$pid][$programId]);
                $hasNewType        = ! isset($existingTypePivot[$pid][$typeId]);
                $hasNewAffiliation = $affiliationId && ! isset($existingAffiliationPivot[$pid][$affiliationId]);

                if ($hasNewProgram || $hasNewType || $hasNewAffiliation) {
                    if ($hasNewProgram) {
                        $existingUpdates[$pid]['programs'][] = $programId;
                        $existingPivot[$pid][$programId]     = true;
                    }
                    if ($hasNewType) {
                        $existingUpdates[$pid]['types'][] = $typeId;
                        $existingTypePivot[$pid][$typeId] = true;
                    }
                    if ($hasNewAffiliation) {
                        $existingUpdates[$pid]['affiliations'][]              = $affiliationId;
                        $existingAffiliationPivot[$pid][$affiliationId]       = true;
                    }
                } else {
                    $skipped[] = $this->skippedRow(
                        $rawValues, $headers,
                        'Participante ya registrado (sin datos nuevos que agregar)'
                    );
                }
                continue;
            }

            // ── Mismo doc ya visto en este archivo ────────────────────────
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

            // ── Email duplicado ───────────────────────────────────────────
            if ($email !== null && isset($existingEmails[$email])) {
                $skipped[] = $this->skippedRow($rawValues, $headers, "Correo duplicado ({$email})");
                continue;
            }

            // ── Nuevo participante ────────────────────────────────────────
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

            $existingDocToId[$document] = 0;
            if ($email) {
                $existingEmails[$email] = true;
            }
        }

        // ── Insertar nuevos participantes en lotes ────────────────────────
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

            $programBatch = [];
            $typeBatch    = [];

            foreach ($docKeys as $doc) {
                $pid = $docToId[$doc] ?? null;
                if (! $pid) {
                    continue;
                }

                foreach (($newPrograms[$doc] ?? []) as $programId) {
                    $programBatch[] = ['participant_id' => $pid, 'program_id' => $programId, 'created_at' => $now, 'updated_at' => $now];
                    if (count($programBatch) === self::BATCH_SIZE) {
                        DB::table('participant_program')->insert($programBatch);
                        $programBatch = [];
                    }
                }

                foreach (($newTypes[$doc] ?? []) as $typeId) {
                    $typeBatch[] = ['participant_id' => $pid, 'participant_type_id' => $typeId, 'created_at' => $now, 'updated_at' => $now];
                    if (count($typeBatch) === self::BATCH_SIZE) {
                        DB::table('participant_type_participant')->insert($typeBatch);
                        $typeBatch = [];
                    }
                }
            }
            if (! empty($programBatch)) {
                DB::table('participant_program')->insert($programBatch);
            }
            if (! empty($typeBatch)) {
                DB::table('participant_type_participant')->insert($typeBatch);
            }

            $affiliationBatch = [];
            foreach ($docKeys as $doc) {
                $pid = $docToId[$doc] ?? null;
                if (! $pid) {
                    continue;
                }
                foreach (($newAffiliations[$doc] ?? []) as $affiliationId) {
                    $affiliationBatch[] = ['participant_id' => $pid, 'affiliation_id' => $affiliationId, 'created_at' => $now, 'updated_at' => $now];
                    if (count($affiliationBatch) === self::BATCH_SIZE) {
                        DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
                        $affiliationBatch = [];
                    }
                }
            }
            if (! empty($affiliationBatch)) {
                DB::table('affiliation_participant')->insertOrIgnore($affiliationBatch);
            }
        }

        // ── Actualizar participantes existentes ───────────────────────────
        $programsAttached     = 0;
        $typesAttached        = 0;
        $existingProgramBatch     = [];
        $existingTypeBatch        = [];
        $existingAffiliationBatch = [];

        foreach ($existingUpdates as $pid => $updates) {
            foreach (array_unique($updates['programs'] ?? []) as $programId) {
                $existingProgramBatch[] = ['participant_id' => $pid, 'program_id' => $programId, 'created_at' => $now, 'updated_at' => $now];
                $programsAttached++;
                if (count($existingProgramBatch) === self::BATCH_SIZE) {
                    DB::table('participant_program')->insertOrIgnore($existingProgramBatch);
                    $existingProgramBatch = [];
                }
            }
            foreach (array_unique($updates['types'] ?? []) as $typeId) {
                $existingTypeBatch[] = ['participant_id' => $pid, 'participant_type_id' => $typeId, 'created_at' => $now, 'updated_at' => $now];
                $typesAttached++;
                if (count($existingTypeBatch) === self::BATCH_SIZE) {
                    DB::table('participant_type_participant')->insertOrIgnore($existingTypeBatch);
                    $existingTypeBatch = [];
                }
            }
            foreach (array_unique($updates['affiliations'] ?? []) as $affiliationId) {
                $existingAffiliationBatch[] = ['participant_id' => $pid, 'affiliation_id' => $affiliationId, 'created_at' => $now, 'updated_at' => $now];
                if (count($existingAffiliationBatch) === self::BATCH_SIZE) {
                    DB::table('affiliation_participant')->insertOrIgnore($existingAffiliationBatch);
                    $existingAffiliationBatch = [];
                }
            }
        }
        if (! empty($existingProgramBatch)) {
            DB::table('participant_program')->insertOrIgnore($existingProgramBatch);
        }
        if (! empty($existingTypeBatch)) {
            DB::table('participant_type_participant')->insertOrIgnore($existingTypeBatch);
        }
        if (! empty($existingAffiliationBatch)) {
            DB::table('affiliation_participant')->insertOrIgnore($existingAffiliationBatch);
        }

        session(['import_skipped' => $skipped]);

        return redirect()->route('participants-import.index')
            ->with('import_result', [
                'saved'             => $saved,
                'programs_attached' => $programsAttached,
                'types_attached'    => $typesAttached,
                'skipped'           => count($skipped),
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
            'first_name'     => ucwords(strtolower(trim($request->first_name))),
            'last_name'      => ucwords(strtolower(trim($request->last_name))),
            'email'          => $request->email ?: null,
            'student_code'   => $request->student_code ?: null,
            'gender'         => $request->sexo ?: null,
            'priority_group' => $request->priority_group ?: null,
        ]);

        // Attach affiliation via pivot
        if ($request->role === 'Docente' && $request->affiliation_id) {
            $participant->affiliations()->attach($request->affiliation_id);
        }

        // Attach to type pivot
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
