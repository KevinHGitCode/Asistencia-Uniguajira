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
    // Columnas del Excel (mismo orden que seed.xlsx)
    private const EXCEL_HEADERS = [
        'Documento',
        'Nombres',
        'Apellidos',
        'Rol',
        'Correo',
        'Programa - Sede',
        'Tipo Programa',
        'Afiliación',
    ];

    public function index()
    {
        $programs     = Program::orderBy('name')->get(['id', 'name', 'campus']);
        $affiliations = Affiliation::orderBy('name')->get(['id', 'name']);

        return view('administration.participants.index', compact('programs', 'affiliations'));
    }

    // Tamaño de lote para inserciones masivas
    private const BATCH_SIZE = 500;

    /**
     * Procesa el archivo Excel y guarda los participantes válidos en lotes.
     * Los omitidos se almacenan en sesión para su descarga posterior.
     */
    public function import(Request $request)
    {
        // Eliminar límite de tiempo y asegurar memoria suficiente para archivos grandes
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

        // Saltar cabecera
        array_shift($rows);

        // ── Cachés de lookup (una sola consulta cada uno) ──────────────────
        $programHash = [];
        foreach (Program::all(['id', 'name', 'campus']) as $program) {
            $key = strtolower($program->name) . '|' . strtolower($program->campus ?? '');
            $programHash[$key] = $program->id;
        }

        $affiliationHash = [];
        foreach (Affiliation::all(['id', 'name']) as $aff) {
            $affiliationHash[strtolower($aff->name)] = $aff->id;
        }

        // Sets de unicidad existentes en BD (flip convierte valores en claves para O(1) lookup)
        $existingDocuments = DB::table('participants')
            ->pluck('document')
            ->flip()
            ->toArray();

        $existingEmails = DB::table('participants')
            ->whereNotNull('email')
            ->pluck('email')
            ->flip()
            ->toArray();

        // ── Variables de estado ─────────────────────────────────────────────
        $now     = now()->toDateTimeString(); // timestamp único, no llamar now() en cada fila
        $batch   = [];
        $skipped = [];
        $saved   = 0;

        $validRoles = ['Estudiante', 'Docente', 'Administrativo', 'Graduado', 'Comunidad Externa'];

        foreach ($rows as $row) {
            // Ignorar filas completamente vacías
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

            // Normalizar rol inválido a Comunidad Externa
            if (! in_array($roleName, $validRoles, true)) {
                $roleName = 'Comunidad Externa';
            }

            // Resolver program_id desde caché
            $programId = null;
            if (! empty($programName)) {
                $parts      = explode(' - ', (string) $programName, 2);
                $programKey = strtolower(trim($parts[0])) . '|' . strtolower(trim($parts[1] ?? ''));
                $programId  = $programHash[$programKey] ?? null;
            }

            // Resolver affiliation_id (crea la afiliación si es nueva — ocurre raramente)
            $affiliationId = null;
            if (! empty($affiliationType) && $affiliationType !== '0' && $affiliationType !== 0) {
                $affKey = strtolower(trim((string) $affiliationType));
                if (! isset($affiliationHash[$affKey])) {
                    $newAff = Affiliation::create(['name' => trim((string) $affiliationType)]);
                    $affiliationHash[$affKey] = $newAff->id;
                }
                $affiliationId = $affiliationHash[$affKey];
            }

            // ── Detección de conflictos ─────────────────────────────────────
            $conflicts = [];

            if ($document === '') {
                $conflicts[] = 'Documento vacío';
            } elseif (isset($existingDocuments[$document])) {
                $conflicts[] = "Documento duplicado ({$document})";
            }

            if ($email !== null && isset($existingEmails[$email])) {
                $conflicts[] = "Correo duplicado ({$email})";
            }

            if (! empty($conflicts)) {
                $row['_motivo'] = implode('; ', $conflicts);
                $skipped[]      = $row;
                continue;
            }

            // ── Acumular en el lote ─────────────────────────────────────────
            $batch[] = [
                'document'         => $document,
                'student_code'     => null,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'email'            => $email ?: null,
                'role'             => $roleName,
                'affiliation_id'   => $affiliationId,
                'sexo'             => null,
                'grupo_priorizado' => null,
                'program_id'       => $programId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            // Actualizar sets de unicidad locales para detectar duplicados dentro del mismo archivo
            $existingDocuments[$document] = true;
            if ($email) {
                $existingEmails[$email] = true;
            }

            // Vaciar lote cada BATCH_SIZE filas
            if (count($batch) === self::BATCH_SIZE) {
                DB::table('participants')->insert($batch);
                $saved += self::BATCH_SIZE;
                $batch  = [];
            }
        }

        // Insertar el lote restante
        if (! empty($batch)) {
            DB::table('participants')->insert($batch);
            $saved += count($batch);
        }

        // Guardar omitidos en sesión para descarga posterior
        session(['import_skipped' => $skipped]);

        return redirect()->route('participants-import.index')
            ->with('import_result', [
                'saved'   => $saved,
                'skipped' => count($skipped),
            ]);
    }

    /**
     * Descarga un Excel con las filas que fueron omitidas en la última importación.
     */
    public function downloadSkipped()
    {
        $skipped = session('import_skipped', []);

        if (empty($skipped)) {
            return redirect()->route('participants-import.index')
                ->with('error', 'No hay datos omitidos disponibles para descargar.');
        }

        // Construir el Excel usando arrays simples
        $export = new \App\Exports\SkippedParticipantsExport($skipped);

        return Excel::download($export, 'participantes_omitidos_' . now()->format('Ymd_His') . '.xlsx');
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
            'document.required'     => 'El documento es obligatorio.',
            'document.unique'       => 'Ya existe un participante con ese documento.',
            'first_name.required'   => 'El nombre es obligatorio.',
            'last_name.required'    => 'El apellido es obligatorio.',
            'email.email'           => 'El correo no tiene un formato válido.',
            'email.unique'          => 'Ya existe un participante con ese correo.',
            'role.required'         => 'El rol es obligatorio.',
            'student_code.unique'   => 'Ya existe un participante con ese código estudiantil.',
            'affiliation_id.exists' => 'La afiliación seleccionada no existe.',
            'program_id.exists'     => 'El programa seleccionado no existe.',
        ]);

        Participant::create([
            'document'         => trim($request->document),
            'first_name'       => ucwords(strtolower(trim($request->first_name))),
            'last_name'        => ucwords(strtolower(trim($request->last_name))),
            'email'            => $request->email ?: null,
            'role'             => $request->role,
            'student_code'     => $request->student_code ?: null,
            'affiliation_id'   => in_array($request->role, ['Docente'])
                                    ? $request->affiliation_id
                                    : null,
            'program_id'       => in_array($request->role, ['Estudiante', 'Graduado'])
                                    ? $request->program_id
                                    : null,
            'sexo'             => $request->sexo ?: null,
            'grupo_priorizado' => $request->grupo_priorizado ?: null,
        ]);

        return redirect()->route('participants-import.index')
            ->with('success', 'Participante registrado exitosamente.');
    }
}
