<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Participant;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImportController extends Controller
{
    private const BATCH_SIZE = 500;

    public function index()
    {
        $programs     = Program::orderBy('name')->get(['id', 'name', 'campus']);
        $affiliations = Affiliation::orderBy('name')->get(['id', 'name']);

        return view('administration.participants.index', compact('programs', 'affiliations'));
    }

    /**
     * Procesa el Excel agrupando por documento:
     *   - Mismo doc + nuevo programa → adjunta programa (no es error).
     *   - Doc nuevo → inserta participante + pivot.
     *   - Doc existente sin programa nuevo → omitido.
     *   - Email duplicado (solo nuevos) → omitido.
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

        $sheets = Excel::toArray([], $request->file('excel_file'));
        $rows   = $sheets[0] ?? [];
        array_shift($rows);

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

        $now        = now()->toDateTimeString();
        $validRoles = ['Estudiante', 'Docente', 'Administrativo', 'Graduado', 'Comunidad Externa'];

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

            [$document, $firstName, $lastName, $roleName, $email, $programName, , $affiliationType]
                = array_pad($rawValues, 8, null);

            $document  = trim((string) ($document ?? ''));
            $firstName = ucwords(strtolower(trim((string) ($firstName ?? ''))));
            $lastName  = ucwords(strtolower(trim((string) ($lastName ?? ''))));
            $roleName  = trim((string) ($roleName ?? ''));
            $email     = $email ? strtolower(trim((string) $email)) : null;

            if ($document === '') {
                $row['_motivo'] = 'Documento vacío';
                $skipped[]      = $row;
                continue;
            }

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
                    $aff                      = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $aff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── Doc ya existe en BD ───────────────────────────────────────
            if (isset($existingDocToId[$document])) {
                $pid = $existingDocToId[$document];
                if ($programId && ! isset($existingPivot[$pid][$programId])) {
                    $existingUpdates[$pid][]          = $programId;
                    $existingPivot[$pid][$programId] = true;
                } else {
                    $row['_motivo'] = 'Participante ya registrado (sin programa nuevo que agregar)';
                    $skipped[]      = $row;
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
                $row['_motivo'] = "Correo duplicado ({$email})";
                $skipped[]      = $row;
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
            $newPrograms[$document]    = $programId ? [$programId] : [];

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
            $docToId    = DB::table('participants')
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
                    $pivotBatch[] = ['participant_id' => $pid, 'program_id' => $programId,
                                     'created_at' => $now, 'updated_at' => $now];
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
                $existingPivotBatch[] = ['participant_id' => $pid, 'program_id' => $programId,
                                          'created_at' => $now, 'updated_at' => $now];
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
        $request->validate([
            'document'         => 'required|string|max:20|unique:participants,document',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'nullable|email|max:255|unique:participants,email',
            'role'             => 'required|in:Estudiante,Docente,Administrativo,Graduado,Comunidad Externa',
            'student_code'     => 'nullable|string|max:20|unique:participants,student_code',
            'affiliation_id'   => 'nullable|exists:affiliations,id',
            'program_id'       => 'nullable|exists:programs,id',
            'sexo'             => 'nullable|in:Masculino,Femenino,No binario',
            'grupo_priorizado' => 'nullable|string|max:100',
        ], [
            'document.required'   => 'El documento es obligatorio.',
            'document.unique'     => 'Ya existe un participante con ese documento.',
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required'  => 'El apellido es obligatorio.',
            'email.email'         => 'El correo no tiene un formato válido.',
            'email.unique'        => 'Ya existe un participante con ese correo.',
            'role.required'       => 'El rol es obligatorio.',
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
}
