<?php

namespace App\Http\Controllers\Configuration;

use App\Exceptions\ImportParseException;
use App\Http\Controllers\Controller;
use App\Jobs\ParseParticipantImportJob;
use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\ImportBatch;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Services\ActivityLogService;
use App\Services\CampusScopeService;
use App\Services\ParticipantImportParser;
use App\Support\ImportContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImportController extends Controller
{
    private const BATCH_SIZE = 500;

    /**
     * Umbral (bytes) sobre el cual un .xlsx se procesa en cola en vez de inline.
     * Los CSV siempre van inline (fast-path nativo, ~8× más rápido). Por debajo
     * del umbral el parseo es lo bastante rápido para responder dentro del request
     * y evitar la latencia del cron en Hostinger (ADR-0004).
     */
    private const QUEUE_THRESHOLD_BYTES = 262144; // 256 KB

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

        // Lotes de importación pendientes de revisión o aún procesándose (ADR-0004).
        $pendingBatches = ImportBatch::whereIn('status', ['en_revision', 'procesando'])
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

    public function downloadExport()
    {
        ActivityLogService::log('exportar', 'participantes', 'Descargó el listado actual de participantes en formato de importación');

        return Excel::download(
            new \App\Exports\ParticipantExport,
            'participantes_actuales_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    /**
     * Recibe el archivo, crea el lote y lanza el parseo a staging (ADR-0004).
     *
     * Híbrido: los CSV y los .xlsx pequeños se parsean inline (respuesta
     * inmediata, sin latencia de cron); los .xlsx grandes se encolan y se
     * procesan en segundo plano, avisando al usuario al terminar (ADR-0018).
     */
    public function import(Request $request, CampusScopeService $campusScope, ParticipantImportParser $parser)
    {
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

        $uploaded = $request->file('excel_file');
        $extension = strtolower($uploaded->getClientOriginalExtension());
        $originalName = $uploaded->getClientOriginalName();
        $sizeBytes = (int) ($uploaded->getSize() ?? 0);

        // Resolver el contexto de sede AHORA (en el request): si el usuario no
        // tiene sede asignada, esto lanza ValidationException y se muestra en el
        // formulario, antes de crear nada.
        $defaultCampusId = $this->defaultCampusIdForImport($request, $campusScope);
        $ctx = ImportContext::fromUser($request->user(), $defaultCampusId);

        // Guardar el archivo en disco para que el parseo (inline o en cola) lo lea.
        $disk = 'local';
        $relativePath = $uploaded->store('imports', $disk);
        $absolutePath = Storage::disk($disk)->path($relativePath);

        $batch = ImportBatch::create([
            'user_id' => $request->user()->id,
            'original_filename' => $originalName,
            'status' => 'procesando',
        ]);

        $isCsv = in_array($extension, ['csv', 'txt'], true);
        $shouldQueue = ! $isCsv && $sizeBytes > self::QUEUE_THRESHOLD_BYTES;

        if ($shouldQueue) {
            ParseParticipantImportJob::dispatch($batch, $disk, $relativePath, $extension, $ctx);

            return redirect()
                ->route('participants-import.review', $batch)
                ->with('success', 'Tu archivo se está procesando en segundo plano. Puedes seguir usando el sistema; te avisaremos cuando esté listo para revisar.');
        }

        // ── Inline (CSV o .xlsx pequeño) ──────────────────────────────────
        try {
            $parser->parse($batch, $absolutePath, $extension, $ctx);
        } catch (ImportParseException $e) {
            // Error de formato del usuario: no dejamos un lote huérfano.
            $batch->delete();
            Storage::disk($disk)->delete($relativePath);

            return back()->withErrors(['excel_file' => $e->getMessage()]);
        }

        Storage::disk($disk)->delete($relativePath);

        return redirect()
            ->route('participants-import.review', $batch)
            ->with('success', 'Archivo procesado. Revisa los registros antes de confirmar: nada se guarda hasta que apruebes el lote.');
    }

    /**
     * Estado del lote en JSON, para el poll de la vista de revisión mientras el
     * parseo corre en segundo plano (ADR-0004).
     */
    public function status(ImportBatch $batch)
    {
        return response()->json([
            'status' => $batch->status,
            'new' => $batch->new_count,
            'update' => $batch->update_count,
            'skipped' => $batch->skipped_count,
            'error' => $batch->error_message,
        ]);
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
                    $key = $this->roleStorageKey([
                        'participant_type_id' => $r->participant_type_id,
                        'program_id' => $r->program_id,
                        'dependency_id' => $r->dependency_id,
                        'affiliation_id' => $r->affiliation_id,
                    ]);
                    $activeRoles[$r->participant_id][$key] = [
                        'id' => $r->id,
                        'affiliation_id' => $r->affiliation_id,
                    ];
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
            $toUpdate = array_intersect($wantedKeys, $currentKeys);

            $changed = false;

            foreach ($toUpdate as $roleKey) {
                $role = $wantedRoles[$roleKey];
                $currentRole = $currentRoleKeys[$roleKey] ?? null;

                if ($currentRole && (int) ($currentRole['affiliation_id'] ?? 0) !== (int) ($role['affiliation_id'] ?? 0)) {
                    DB::table('participant_roles')
                        ->where('id', $currentRole['id'])
                        ->update([
                            'affiliation_id' => $role['affiliation_id'],
                            'updated_at' => $now,
                        ]);
                    $changed = true;
                }
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
                    ->where('is_active', 0)
                    ->update([
                        'affiliation_id' => $role['affiliation_id'],
                        'is_active' => 1,
                        'updated_at' => $now,
                    ]);

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

    private function roleStorageKey(array $role): string
    {
        $typeId = (int) ($role['participant_type_id'] ?? 0);

        if (! empty($role['program_id'])) {
            return $typeId.'|program|'.(int) $role['program_id'];
        }

        if (! empty($role['dependency_id'])) {
            return $typeId.'|dependency|'.(int) $role['dependency_id'];
        }

        return $typeId.'|affiliation|'.(int) ($role['affiliation_id'] ?? 0);
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

        if (in_array($batch->status, ['procesando', 'error'], true)) {
            return view('administration.participants.review', [
                'batch' => $batch,
                'rows' => null,
                'estado' => $estado,
                'typeNames' => collect(),
                'programNames' => collect(),
                'dependencyNames' => collect(),
                'affiliationNames' => collect(),
            ]);
        }

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
                $role = [
                    'participant_type_id' => $r['participant_type_id'] ?? null,
                    'program_id' => $r['program_id'] ?? null,
                    'dependency_id' => $r['dependency_id'] ?? null,
                    'affiliation_id' => $r['affiliation_id'] ?? null,
                ];
                $roles[$this->roleStorageKey($role)] = $role;
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

        $result = DB::transaction(function () use (&$newParticipants, &$newRoles, &$excelRolesForExisting) {
            $reconcileResult = $this->reconcileNewParticipantsForApproval($newParticipants, $newRoles, $excelRolesForExisting);
            $commitResult = $this->commitPlan($newParticipants, $newRoles, $excelRolesForExisting);

            return $commitResult + $reconcileResult;
        });

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
     * Un lote puede quedar desactualizado entre la revisión y la aprobación:
     * otro admin pudo crear/aprobar el mismo participante primero. Antes del
     * insert final, convierte esos "nuevo" en actualizaciones de roles y omite
     * conflictos de correo que pertenezcan a otro documento.
     */
    private function reconcileNewParticipantsForApproval(array &$newParticipants, array &$newRoles, array &$excelRolesForExisting): array
    {
        if (empty($newParticipants)) {
            return ['participants_skipped_conflict' => 0];
        }

        $documents = array_keys($newParticipants);
        $emails = collect($newParticipants)
            ->pluck('email')
            ->filter()
            ->map(fn ($email) => mb_strtolower((string) $email, 'UTF-8'))
            ->unique()
            ->values()
            ->all();

        $existingByDocument = [];
        foreach (array_chunk($documents, 500) as $chunk) {
            DB::table('participants')
                ->whereIn('document', $chunk)
                ->get(['id', 'document'])
                ->each(function ($participant) use (&$existingByDocument) {
                    $existingByDocument[$participant->document] = (int) $participant->id;
                });
        }

        $existingByEmail = [];
        foreach (array_chunk($emails, 500) as $chunk) {
            DB::table('participants')
                ->whereNotNull('email')
                ->whereIn('email', $chunk)
                ->get(['id', 'document', 'email'])
                ->each(function ($participant) use (&$existingByEmail) {
                    $existingByEmail[mb_strtolower((string) $participant->email, 'UTF-8')] = [
                        'id' => (int) $participant->id,
                        'document' => (string) $participant->document,
                    ];
                });
        }

        $skippedConflicts = 0;

        foreach ($newParticipants as $document => $participant) {
            $participantId = $existingByDocument[$document] ?? null;

            if ($participantId) {
                $excelRolesForExisting[$participantId] = array_replace(
                    $excelRolesForExisting[$participantId] ?? [],
                    $newRoles[$document] ?? [],
                );
                unset($newParticipants[$document], $newRoles[$document]);

                continue;
            }

            $email = $participant['email'] ? mb_strtolower((string) $participant['email'], 'UTF-8') : null;
            $emailOwner = $email ? ($existingByEmail[$email] ?? null) : null;

            if ($emailOwner && $emailOwner['document'] !== (string) $document) {
                unset($newParticipants[$document], $newRoles[$document]);
                $skippedConflicts++;
            }
        }

        return ['participants_skipped_conflict' => $skippedConflicts];
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
}
