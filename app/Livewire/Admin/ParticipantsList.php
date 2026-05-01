<?php

namespace App\Livewire\Admin;

use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\Attendance;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantsList extends Component
{
    use WithPagination;

    public string $search = '';

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

    // ── Eliminación ─────────────────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public string $deletingName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Editar ───────────────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $participant = Participant::with(['activeRoles'])->findOrFail($id);

        $this->editingId       = $participant->id;
        $this->editDocument    = $participant->document;
        $this->editFirstName   = $participant->first_name;
        $this->editLastName    = $participant->last_name;
        $this->editEmail       = $participant->email ?? '';
        $this->editStudentCode = $participant->student_code ?? '';

        // Cargar catálogos
        $this->catalogTypes        = ParticipantType::orderBy('name')->get(['id', 'name'])->toArray();
        $this->catalogPrograms     = Program::orderBy('name')->get(['id', 'name'])->toArray();
        $this->catalogDependencies = Dependency::orderBy('name')->get(['id', 'name'])->toArray();
        $this->catalogAffiliations = Affiliation::orderBy('name')->get(['id', 'name'])->toArray();

        // Cargar roles existentes
        $this->editRoles = $participant->activeRoles->map(fn ($role) => [
            'id'                  => $role->id,
            'participant_type_id' => (string) $role->participant_type_id,
            'program_id'          => (string) ($role->program_id ?? ''),
            'dependency_id'       => (string) ($role->dependency_id ?? ''),
            'affiliation_id'      => (string) ($role->affiliation_id ?? ''),
            'is_active'           => $role->is_active,
        ])->toArray();

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
            'is_active'           => true,
        ];
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

        return 'none';
    }

    public function updateParticipant(): void
    {
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

        DB::transaction(function () use ($participant) {
            // Actualizar datos básicos
            $participant->update([
                'document'     => trim($this->editDocument),
                'first_name'   => mb_convert_case(mb_strtolower(trim($this->editFirstName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'last_name'    => mb_convert_case(mb_strtolower(trim($this->editLastName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
                'email'        => $this->editEmail ?: null,
                'student_code' => $this->editStudentCode ?: null,
            ]);

            // Sincronizar roles
            $existingRoleIds = [];

            foreach ($this->editRoles as $roleData) {
                $typeCategory = $this->getTypeCategory($roleData['participant_type_id']);

                $programId     = null;
                $dependencyId  = null;
                $affiliationId = null;

                if ($typeCategory === 'program') {
                    $programId     = $roleData['program_id'] ?: null;
                    $affiliationId = $roleData['affiliation_id'] ?: null;
                }
                if ($typeCategory === 'dependency') {
                    $dependencyId  = $roleData['dependency_id'] ?: null;
                    $affiliationId = $roleData['affiliation_id'] ?: null;
                }

                if (!empty($roleData['id'])) {
                    // Actualizar rol existente
                    ParticipantRole::where('id', $roleData['id'])
                        ->where('participant_id', $participant->id)
                        ->update([
                            'participant_type_id' => $roleData['participant_type_id'],
                            'program_id'          => $programId,
                            'dependency_id'       => $dependencyId,
                            'affiliation_id'      => $affiliationId,
                            'is_active'           => true,
                            'updated_at'          => now(),
                        ]);
                    $existingRoleIds[] = $roleData['id'];
                } else {
                    // Crear nuevo rol
                    $newRole = ParticipantRole::create([
                        'participant_id'      => $participant->id,
                        'participant_type_id' => $roleData['participant_type_id'],
                        'program_id'          => $programId,
                        'dependency_id'       => $dependencyId,
                        'affiliation_id'      => $affiliationId,
                        'is_active'           => true,
                    ]);
                    $existingRoleIds[] = $newRole->id;
                }
            }

            // Desactivar roles que ya no están en la lista
            ParticipantRole::where('participant_id', $participant->id)
                ->whereNotIn('id', $existingRoleIds)
                ->update(['is_active' => false]);
        });

        $this->showEditModal = false;
        $this->resetEditFields();

        session()->flash('participant-success', 'Participante actualizado exitosamente.');
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

        $participant->delete();

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
        $participants = Participant::with([
            'activeRoles.type',
            'activeRoles.program',
            'activeRoles.dependency',
            'activeRoles.affiliation',
        ])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('document', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return view('livewire.admin.participants-list', compact('participants'));
    }
}
