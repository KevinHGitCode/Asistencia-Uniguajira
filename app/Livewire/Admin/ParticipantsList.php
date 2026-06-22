<?php

namespace App\Livewire\Admin;

use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\Attendance;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantsList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $filterUnclassified = false;

    // ── Edición ──────────────────────────────────────────────────────────
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public string $editDocument = '';
    public string $editFirstName = '';
    public string $editLastName = '';
    public ?string $editEmail = '';
    public ?string $editStudentCode = '';

    // ── Roles del participante en edición ────────────────────────────────
    /** @var array<int, array{id: ?int, participant_type_id: string, program_id: string, dependency_id: string, affiliation_id: string, is_active: bool}> */
    public array $editRoles = [];

    // ── Catálogos para selects ───────────────────────────────────────────
    public array $catalogTypes = [];
    public array $catalogPrograms = [];
    public array $catalogDependencies = [];
    public array $catalogAffiliations = [];

    // ── Estamentos que se ligan a dependencia + vinculación ─────────────
    private const DEPENDENCY_TYPE_NAMES = ['Administrativo'];

    // ── Estamentos que se ligan a programa + vinculación ─────────────────
    private const PROGRAM_TYPE_NAMES = ['Estudiante', 'Graduado', 'Docente'];

    // ── Estamentos que se ligan a organización ──────────────────────────
    private const ORGANIZATION_TYPE_NAMES = ['Comunidad Externa'];

    // ── Error de duplicados (se muestra dentro del modal) ──────────────
    public string $roleError = '';

    // ── Búsqueda de organizaciones para roles ───────────────────────────
    public array $organizationSuggestions = [];
    public int $organizationSearchIndex = -1;

    // ── Eliminación ─────────────────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public string $deletingName = '';

    public function mount(): void
    {
        if (request()->query('filtro') === 'sin_clasificar') {
            $this->filterUnclassified = true;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUnclassified(): void
    {
        $this->resetPage();
    }

    public function toggleUnclassifiedFilter(): void
    {
        $this->filterUnclassified = ! $this->filterUnclassified;
        $this->resetPage();
    }

    // ── Editar ───────────────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $participant = Participant::with(['activeRoles.organization'])->findOrFail($id);

        $this->editingId       = $participant->id;
        $this->editDocument    = $participant->document;
        $this->editFirstName   = $participant->first_name;
        $this->editLastName    = $participant->last_name;
        $this->editEmail       = $participant->email ?? '';
        $this->editStudentCode = $participant->student_code ?? '';

        // Cargar catálogos
        $this->catalogTypes        = ParticipantType::orderBy('name')->get(['id', 'name'])->toArray();
        $this->catalogPrograms     = Program::orderBy('name')->get(['id', 'name'])->toArray();
        $this->catalogDependencies = Dependency::query()
            ->with('campus:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'campus_id'])
            ->map(fn (Dependency $dependency) => [
                'id' => $dependency->id,
                'name' => $dependency->name.($dependency->campus?->name ? ' - '.$dependency->campus->name : ''),
            ])
            ->all();
        $this->catalogAffiliations = Affiliation::orderBy('name')->get(['id', 'name'])->toArray();

        // Cargar roles existentes
        $this->editRoles = $participant->activeRoles->map(fn ($role) => [
            'id'                  => $role->id,
            'participant_type_id' => (string) $role->participant_type_id,
            'program_id'          => (string) ($role->program_id ?? ''),
            'dependency_id'       => (string) ($role->dependency_id ?? ''),
            'affiliation_id'      => (string) ($role->affiliation_id ?? ''),
            'organization_id'     => (string) ($role->organization_id ?? ''),
            'organization_name'   => $role->organization?->name ?? '',
            'is_active'           => $role->is_active,
        ])->toArray();

        $this->organizationSuggestions = [];
        $this->organizationSearchIndex = -1;
        $this->roleError = '';

        // Si no tiene roles, agregar uno vacío
        if (empty($this->editRoles)) {
            $this->addRole();
        }

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function addRole(): void
    {
        $this->editRoles[] = [
            'id'                  => null,
            'participant_type_id' => '',
            'program_id'          => '',
            'dependency_id'       => '',
            'affiliation_id'      => '',
            'organization_id'     => '',
            'organization_name'   => '',
            'is_active'           => true,
        ];
    }

    public function searchOrganizations(int $index, string $term): void
    {
        $this->organizationSearchIndex = $index;
        $term = trim($term);

        // Limpiar organization_id cuando el usuario escribe
        if (isset($this->editRoles[$index])) {
            $this->editRoles[$index]['organization_id'] = '';
        }

        if (mb_strlen($term) < 2) {
            $this->organizationSuggestions = [];
            return;
        }

        $this->organizationSuggestions = Organization::where('name', 'LIKE', "%{$term}%")
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name'])
            ->toArray();
    }

    public function selectRoleOrganization(int $index, int $orgId, string $orgName): void
    {
        if (isset($this->editRoles[$index])) {
            $this->editRoles[$index]['organization_id']   = (string) $orgId;
            $this->editRoles[$index]['organization_name']  = $orgName;
        }
        $this->organizationSuggestions = [];
        $this->organizationSearchIndex = -1;
    }

    public function removeRole(int $index): void
    {
        if (count($this->editRoles) <= 1) {
            return;
        }

        unset($this->editRoles[$index]);
        $this->editRoles = array_values($this->editRoles);
    }

    /**
     * Determina qué campo asociado mostrar según el tipo de estamento.
     */
    public function getTypeCategory(string $typeId): string
    {
        if ($typeId === '') {
            return 'none';
        }

        $typeName = collect($this->catalogTypes)->firstWhere('id', (int) $typeId)['name'] ?? '';

        if (in_array($typeName, self::DEPENDENCY_TYPE_NAMES, true)) {
            return 'dependency';
        }

        if (in_array($typeName, self::PROGRAM_TYPE_NAMES, true)) {
            return 'program';
        }

        if (in_array($typeName, self::ORGANIZATION_TYPE_NAMES, true)) {
            return 'organization';
        }

        return 'none';
    }

    public function updateParticipant(): void
    {
        $this->roleError = '';

        $this->validate([
            'editDocument'                      => ['required', 'string', 'max:20', Rule::unique('participants', 'document')->ignore($this->editingId)],
            'editFirstName'                     => 'required|string|max:100',
            'editLastName'                      => 'required|string|max:100',
            'editEmail'                         => ['nullable', 'email', 'max:255', Rule::unique('participants', 'email')->ignore($this->editingId)],
            'editStudentCode'                   => ['nullable', 'string', 'max:20', Rule::unique('participants', 'student_code')->ignore($this->editingId)],
            'editRoles'                         => 'required|array|min:1',
            'editRoles.*.participant_type_id'    => 'required|exists:participant_types,id',
            'editRoles.*.program_id'             => 'nullable|exists:programs,id',
            'editRoles.*.dependency_id'          => 'nullable|exists:dependencies,id',
            'editRoles.*.affiliation_id'         => 'nullable|exists:affiliations,id',
        ], [
            'editDocument.required'                  => 'El documento es obligatorio.',
            'editDocument.unique'                    => 'Ya existe un participante con ese documento.',
            'editFirstName.required'                 => 'El nombre es obligatorio.',
            'editLastName.required'                  => 'El apellido es obligatorio.',
            'editEmail.email'                        => 'El correo no tiene un formato válido.',
            'editEmail.unique'                       => 'Ya existe un participante con ese correo.',
            'editStudentCode.unique'                 => 'Ya existe un participante con ese código estudiantil.',
            'editRoles.required'                     => 'Debe tener al menos un rol.',
            'editRoles.*.participant_type_id.required' => 'El estamento es obligatorio.',
            'editRoles.*.participant_type_id.exists'   => 'El estamento seleccionado no es válido.',
        ]);

        $participant = Participant::findOrFail($this->editingId);
        $original = $participant->only(['document', 'first_name', 'last_name', 'email', 'student_code']);

        // ── IDs de roles que el usuario mantiene en el formulario ────────
        $batchRoleIds = collect($this->editRoles)
            ->filter(fn ($r) => ! empty($r['id']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        // ── Pre-validación de roles duplicados ──────────────────────────
        $reactivations     = [];  // index => ParticipantRole inactivo a reactivar
        $duplicatesToDelete = []; // IDs de duplicados a eliminar (evitar conflicto de índice)
        $reactivatedNames  = [];
        $seenCombos        = [];

        foreach ($this->editRoles as $index => $roleData) {
            $typeCategory = $this->getTypeCategory($roleData['participant_type_id']);

            $entityColumn = match ($typeCategory) {
                'program'      => 'program_id',
                'dependency'   => 'dependency_id',
                'organization' => 'organization_id',
                default        => null,
            };

            $entityValue = match ($typeCategory) {
                'program'      => ! empty($roleData['program_id']) ? (int) $roleData['program_id'] : null,
                'dependency'   => ! empty($roleData['dependency_id']) ? (int) $roleData['dependency_id'] : null,
                'organization' => $this->resolveOrganizationIdForCheck($roleData),
                default        => null,
            };

            if (! $entityColumn || ! $entityValue) {
                continue;
            }

            // — Duplicados internos (dentro del formulario) —
            $comboKey = (int) $roleData['participant_type_id'] . ':' . $entityColumn . ':' . $entityValue;
            if (isset($seenCombos[$comboKey])) {
                $typeName = collect($this->catalogTypes)->firstWhere('id', (int) $roleData['participant_type_id'])['name'] ?? 'Rol';
                $this->roleError = "El rol «{$typeName}» con la misma entidad aparece más de una vez en el formulario.";
                return;
            }
            $seenCombos[$comboKey] = true;

            // — Duplicados en la base de datos (excluir roles del lote actual) —
            $duplicate = ParticipantRole::where('participant_id', $participant->id)
                ->where('participant_type_id', $roleData['participant_type_id'])
                ->where($entityColumn, $entityValue)
                ->whereNotIn('id', $batchRoleIds)
                ->first();

            if (! $duplicate) {
                continue;
            }

            $typeName = collect($this->catalogTypes)->firstWhere('id', (int) $roleData['participant_type_id'])['name'] ?? 'Rol';

            if (empty($roleData['id'])) {
                // Rol nuevo → reactivar el duplicado existente
                $reactivations[$index] = $duplicate;
                if (! $duplicate->is_active) {
                    $reactivatedNames[] = $typeName;
                }
            } else {
                // Rol actualizado → eliminar duplicado para evitar conflicto de índice único
                $duplicatesToDelete[] = $duplicate->id;
            }
        }

        // ── Transacción ─────────────────────────────────────────────────
        DB::transaction(function () use ($participant, $batchRoleIds, $reactivations, $duplicatesToDelete) {
            // 1. Actualizar datos básicos
            $participant->update([
                'document'     => trim($this->editDocument),
                'first_name'   => mb_convert_case(mb_strtolower(trim($this->editFirstName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'last_name'    => mb_convert_case(mb_strtolower(trim($this->editLastName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'email'        => $this->editEmail ?: null,
                'student_code' => $this->editStudentCode ?: null,
            ]);

            // 2. Desactivar roles que el usuario eliminó del formulario
            //    (proteger los que serán reactivados)
            $reactivationIds = collect($reactivations)->map(fn ($r) => $r->id)->toArray();
            $protectedIds    = array_merge($batchRoleIds, $reactivationIds);

            ParticipantRole::where('participant_id', $participant->id)
                ->whereNotIn('id', $protectedIds)
                ->update(['is_active' => false]);

            // 3. Eliminar duplicados que conflictarían con actualizaciones
            if (! empty($duplicatesToDelete)) {
                ParticipantRole::whereIn('id', $duplicatesToDelete)->delete();
            }

            // 4. Procesar cada rol
            foreach ($this->editRoles as $roleIndex => $roleData) {
                $typeCategory = $this->getTypeCategory($roleData['participant_type_id']);

                $programId      = null;
                $dependencyId   = null;
                $affiliationId  = null;
                $organizationId = null;

                if ($typeCategory === 'program') {
                    $programId     = $roleData['program_id'] ?: null;
                    $affiliationId = $roleData['affiliation_id'] ?: null;
                }
                if ($typeCategory === 'dependency') {
                    $dependencyId  = $roleData['dependency_id'] ?: null;
                    $affiliationId = $roleData['affiliation_id'] ?: null;
                }
                if ($typeCategory === 'organization') {
                    $organizationId = $roleData['organization_id'] ?: null;
                    if (! $organizationId && ! empty($roleData['organization_name'])) {
                        $normalizedInput = trim($roleData['organization_name']);
                        $org = Organization::whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedInput, 'UTF-8')])->first()
                            ?? Organization::create(['name' => $normalizedInput]);
                        $organizationId = $org->id;
                    }
                }

                $roleFields = [
                    'participant_type_id' => $roleData['participant_type_id'],
                    'program_id'          => $programId,
                    'dependency_id'       => $dependencyId,
                    'affiliation_id'      => $affiliationId,
                    'organization_id'     => $organizationId,
                    'is_active'           => true,
                ];

                if (! empty($roleData['id'])) {
                    // Actualizar rol existente
                    ParticipantRole::where('id', $roleData['id'])
                        ->where('participant_id', $participant->id)
                        ->update(array_merge($roleFields, ['updated_at' => now()]));
                } elseif (isset($reactivations[$roleIndex])) {
                    // Reactivar rol inactivo existente
                    $reactivations[$roleIndex]->update($roleFields);
                } else {
                    // Crear nuevo rol
                    ParticipantRole::create(array_merge(
                        ['participant_id' => $participant->id],
                        $roleFields,
                    ));
                }
            }
        });

        $fullName = trim($participant->first_name . ' ' . $participant->last_name);
        $participant->refresh();
        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $participant->$field;
            if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }
        ActivityLogService::log('editar', 'participantes', "Editó el participante '{$fullName}'", $participant, $changes);

        $this->showEditModal = false;
        $this->resetEditFields();

        if (! empty($reactivatedNames)) {
            $names = implode(', ', $reactivatedNames);
            session()->flash('participant-success', 'Participante actualizado exitosamente.');
            session()->flash('participant-info', "Se reactivaron roles previamente desactivados: {$names}.");
        } else {
            session()->flash('participant-success', 'Participante actualizado exitosamente.');
        }
    }

    /**
     * Resuelve el organization_id para la pre-validación de duplicados
     * sin crear la organización si no existe (solo lectura).
     */
    private function resolveOrganizationIdForCheck(array $roleData): ?int
    {
        if (! empty($roleData['organization_id'])) {
            return (int) $roleData['organization_id'];
        }

        if (! empty($roleData['organization_name'])) {
            $normalized = trim($roleData['organization_name']);
            $org = Organization::whereRaw('LOWER(name) = ?', [mb_strtolower($normalized, 'UTF-8')])->first();

            return $org?->id;
        }

        return null;
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->resetEditFields();
    }

    private function resetEditFields(): void
    {
        $this->editingId       = null;
        $this->editDocument    = '';
        $this->editFirstName   = '';
        $this->editLastName    = '';
        $this->editEmail       = '';
        $this->editStudentCode = '';
        $this->editRoles       = [];
        $this->roleError       = '';
    }

    // ── Eliminar ─────────────────────────────────────────────────────────
    public function openDelete(int $id, string $name): void
    {
        $this->deletingId   = $id;
        $this->deletingName = $name;
        $this->showDeleteModal = true;
    }

    public function deleteParticipant(): void
    {
        $participant = Participant::findOrFail($this->deletingId);

        if (Attendance::where('participant_id', $participant->id)->exists()) {
            $this->showDeleteModal = false;
            $this->deletingId      = null;
            $this->deletingName    = '';
            session()->flash('participant-error', 'No se puede eliminar un participante que tiene asistencias registradas.');
            return;
        }

        DB::table('participant_roles')
            ->where('participant_id', $participant->id)
            ->delete();

        $deletedName = trim($participant->first_name . ' ' . $participant->last_name);
        $participant->delete();

        ActivityLogService::log('eliminar', 'participantes', "Eliminó el participante '{$deletedName}'");

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->deletingName    = '';

        session()->flash('participant-success', 'Participante eliminado exitosamente.');
    }

    public function closeDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->deletingName    = '';
    }

    public function render()
    {
        $query = Participant::with([
            'activeRoles.type',
            'activeRoles.program',
            'activeRoles.dependency',
            'activeRoles.affiliation',
            'activeRoles.organization',
        ])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('document', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });

        // Filtro "Sin clasificar": participantes con al menos una asistencia
        // cuyo rol no tiene programa, dependencia ni organización asignados
        if ($this->filterUnclassified) {
            $unclassifiedIds = DB::table('participants')
                ->join('attendances', 'attendances.participant_id', '=', 'participants.id')
                ->leftJoin('attendance_details', 'attendance_details.attendance_id', '=', 'attendances.id')
                ->leftJoin('participant_roles', 'participant_roles.id', '=', 'attendance_details.participant_role_id')
                ->where(function ($q) {
                    $q->whereNull('attendance_details.id')
                      ->orWhereNull('attendance_details.participant_role_id')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('participant_roles.program_id')
                             ->whereNull('participant_roles.dependency_id')
                             ->whereNull('participant_roles.organization_id');
                      });
                })
                ->distinct()
                ->pluck('participants.id');

            $query->whereIn('id', $unclassifiedIds);
        }

        $participants = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return view('livewire.admin.participants-list', compact('participants'));
    }
}
