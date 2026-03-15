<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Estamento;
use App\Models\Participant;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImportController extends Controller
{
    private const BATCH_SIZE = 500;

    /**
     * Columnas que el Excel DEBE tener (por nombre exacto en la cabecera).
     * Si alguna falta, la importación se rechaza con un mensaje claro.
     */
    private const REQUIRED_COLUMNS = [
        'Documento',
        'Nombres',
        'Apellidos',
        'Tipo de Estamento',
        'Correo',
        'Programa o Dependencia',
        'Vinculacion',
    ];

    /**
     * Mapeo completo: nombre de columna en Excel → campo interno.
     * Las columnas opcionales (Tipo_progama) se leen si existen pero no son obligatorias.
     * Para agregar nuevas columnas en el futuro, añade aquí y en la plantilla.
     */
    private const COLUMN_MAP = [
        'Documento'              => 'document',
        'Nombres'                => 'first_name',
        'Apellidos'              => 'last_name',
        'Tipo de Estamento'      => 'role',
        'Correo'                 => 'email',
        'Programa o Dependencia' => 'program',
        'Tipo_progama'           => 'program_type',   // informativo, no se persiste
        'Vinculacion'            => 'affiliation',
        // Columnas futuras (descomentar cuando se agreguen al Excel):
        // 'Codigo de Estudiante' => 'student_code',
    ];

    public function index()
    {
        $programs     = Program::orderBy('name')->get(['id', 'name', 'campus']);
        $affiliations = Affiliation::orderBy('name')->get(['id', 'name']);
        $estamentos   = Estamento::orderBy('name')->get(['id', 'name']);

        return view('administration.participants.index', compact('programs', 'affiliations', 'estamentos'));
    }

    /**
     * Descarga la plantilla Excel con las cabeceras correctas y una fila de ejemplo.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\ParticipantTemplateExport(),
            'plantilla_participantes.xlsx'
        );
    }

    /**
     * Procesa el Excel agrupando por documento:
     *   - Mismo doc + nuevo programa → adjunta programa (no es error).
     *   - Doc nuevo → inserta participante + pivot.
     *   - Doc existente sin programa nuevo → omitido.
     *   - Email duplicado (solo nuevos) → omitido.
     *   - Tipo de Estamento no válido → omitido.
     *   - Columna requerida faltante en cabecera → error inmediato.
     */
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

        // Construir índice: nombre-columna → posición
        $colIndex = [];
        foreach ($headers as $pos => $name) {
            if ($name !== '') {
                $colIndex[$name] = $pos;
            }
        }

        // Validar columnas obligatorias
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

        // Helper para leer una celda por nombre de columna
        $get = function (array $raw, string $col) use ($colIndex) {
            return isset($colIndex[$col]) ? ($raw[$colIndex[$col]] ?? null) : null;
        };

        // Quitar fila de cabecera
        array_shift($allRows);
        $rows = $allRows;

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

        // Estamentos válidos (desde BD) — clave lowercase → nombre original en BD
        // Permite comparación case-insensitive (ESTUDIANTE, Estudiante, estudiante → 'Estudiante')
        $validEstamentos = [];
        foreach (Estamento::pluck('name') as $name) {
            $validEstamentos[strtolower($name)] = $name;
        }

        $existingDocToId = DB::table('participants')->pluck('id', 'document')->toArray();
        $existingEmails  = DB::table('participants')
            ->whereNotNull('email')->pluck('email')->flip()->toArray();

        // Programas ya asignados en BD [participant_id][program_id] = true
        $existingPivot = [];
        if (! empty($existingDocToId)) {
            DB::table('participant_program')
                ->whereIn('participant_id', array_values($existingDocToId))
                ->get(['participant_id', 'program_id'])
                ->each(function ($row) use (&$existingPivot) {
                    $existingPivot[$row->participant_id][$row->program_id] = true;
                });
        }

        $now = now()->toDateTimeString();

        $newParticipants = [];
        $newPrograms     = [];
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

            // Validar estamento contra la tabla de estamentos (case-insensitive)
            $roleKey = strtolower($roleName);
            if (! isset($validEstamentos[$roleKey])) {
                $skipped[] = $this->skippedRow(
                    $rawValues, $headers,
                    $roleName === ''
                        ? 'Tipo de Estamento vacío'
                        : "Tipo de Estamento no válido: \"{$roleName}\""
                );
                continue;
            }
            // Usar el nombre canónico de la BD (capitalización correcta)
            $roleName = $validEstamentos[$roleKey];

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
                    $aff                      = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── Doc ya existe en BD ───────────────────────────────────────
            if (isset($existingDocToId[$document])) {
                $pid = $existingDocToId[$document];
                if ($programId && ! isset($existingPivot[$pid][$programId])) {
                    $existingUpdates[$pid][]         = $programId;
                    $existingPivot[$pid][$programId] = true;
                } else {
                    $skipped[] = $this->skippedRow(
                        $rawValues, $headers,
                        'Participante ya registrado (sin programa nuevo que agregar)'
                    );
                }
                continue;
            }

            // ── Mismo doc ya visto en este archivo ────────────────────────
            if (isset($newParticipants[$document])) {
                if ($programId && ! in_array($programId, $newPrograms[$document] ?? [], true)) {
                    $newPrograms[$document][] = $programId;
                }
                continue;
            }

            // ── Email duplicado (nuevos participantes) ────────────────────
            if ($email !== null && isset($existingEmails[$email])) {
                $skipped[] = $this->skippedRow($rawValues, $headers, "Correo duplicado ({$email})");
                continue;
            }

            // ── Nuevo participante ────────────────────────────────────────
            $newParticipants[$document] = [
                'document'         => $document,
                'student_code'     => null,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'email'            => $email ?: null,
                'role'             => $roleName,
                'affiliation_id'   => $affiliationId,
                'sexo'             => null,
                'grupo_priorizado' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
            $newPrograms[$document] = $programId ? [$programId] : [];

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

            $pivotBatch = [];
            foreach ($docKeys as $doc) {
                $pid = $docToId[$doc] ?? null;
                if (! $pid) {
                    continue;
                }
                foreach (($newPrograms[$doc] ?? []) as $programId) {
                    $pivotBatch[] = [
                        'participant_id' => $pid,
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

        // ── Adjuntar nuevos programas a participantes existentes ──────────
        $programsAttached   = 0;
        $existingPivotBatch = [];
        foreach ($existingUpdates as $pid => $programIds) {
            foreach (array_unique($programIds) as $programId) {
                $existingPivotBatch[] = [
                    'participant_id' => $pid,
                    'program_id'     => $programId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
                $programsAttached++;
                if (count($existingPivotBatch) === self::BATCH_SIZE) {
                    DB::table('participant_program')->insertOrIgnore($existingPivotBatch);
                    $existingPivotBatch = [];
                }
            }
        }
        if (! empty($existingPivotBatch)) {
            DB::table('participant_program')->insertOrIgnore($existingPivotBatch);
        }

        session(['import_skipped' => $skipped]);

        return redirect()->route('participants-import.index')
            ->with('import_result', [
                'saved'             => $saved,
                'programs_attached' => $programsAttached,
                'skipped'           => count($skipped),
            ]);
    }

    /**
     * Descarga un Excel con las filas omitidas en la última importación.
     */
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

    /**
     * Crea un participante individual.
     */
    public function store(Request $request)
    {
        $validEstamentos = Estamento::pluck('name')->toArray();

        $request->validate([
            'document'         => 'required|string|max:20|unique:participants,document',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'nullable|email|max:255|unique:participants,email',
            'role'             => ['required', 'string', Rule::in($validEstamentos)],
            'student_code'     => 'nullable|string|max:20|unique:participants,student_code',
            'affiliation_id'   => 'nullable|exists:affiliations,id',
            'program_id'       => 'nullable|exists:programs,id',
            'sexo'             => 'nullable|in:Masculino,Femenino,No binario',
            'grupo_priorizado' => 'nullable|string|max:150',
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
            'document'         => trim($request->document),
            'first_name'       => ucwords(strtolower(trim($request->first_name))),
            'last_name'        => ucwords(strtolower(trim($request->last_name))),
            'email'            => $request->email ?: null,
            'role'             => $request->role,
            'student_code'     => $request->student_code ?: null,
            'affiliation_id'   => $request->role === 'Docente' ? $request->affiliation_id : null,
            'sexo'             => $request->sexo ?: null,
            'grupo_priorizado' => $request->grupo_priorizado ?: null,
        ]);

        if ($request->program_id && in_array($request->role, ['Estudiante', 'Graduado'])) {
            $participant->programs()->attach($request->program_id);
        }

        return redirect()->route('participants-import.index')
            ->with('success', 'Participante registrado exitosamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Construye una fila "omitida" como array asociativo
     * (cabecera → valor) más la clave '_motivo'.
     */
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
