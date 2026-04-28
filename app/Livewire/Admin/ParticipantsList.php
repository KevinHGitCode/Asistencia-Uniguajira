<?php

namespace App\Livewire\Admin;

use App\Models\Affiliation;
use App\Models\Dependency;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
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
        $participant = Participant::findOrFail($id);

        $this->editingId       = $participant->id;
        $this->editDocument    = $participant->document;
        $this->editFirstName   = $participant->first_name;
        $this->editLastName    = $participant->last_name;
        $this->editEmail       = $participant->email ?? '';
        $this->editStudentCode = $participant->student_code ?? '';
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateParticipant(): void
    {
        $this->validate([
            'editDocument'    => ['required', 'string', 'max:20', Rule::unique('participants', 'document')->ignore($this->editingId)],
            'editFirstName'   => 'required|string|max:100',
            'editLastName'    => 'required|string|max:100',
            'editEmail'       => ['nullable', 'email', 'max:255', Rule::unique('participants', 'email')->ignore($this->editingId)],
            'editStudentCode' => ['nullable', 'string', 'max:20', Rule::unique('participants', 'student_code')->ignore($this->editingId)],
        ], [
            'editDocument.required'   => 'El documento es obligatorio.',
            'editDocument.unique'     => 'Ya existe un participante con ese documento.',
            'editFirstName.required'  => 'El nombre es obligatorio.',
            'editLastName.required'   => 'El apellido es obligatorio.',
            'editEmail.email'         => 'El correo no tiene un formato válido.',
            'editEmail.unique'        => 'Ya existe un participante con ese correo.',
            'editStudentCode.unique'  => 'Ya existe un participante con ese código estudiantil.',
        ]);

        $participant = Participant::findOrFail($this->editingId);
        $participant->update([
            'document'       => trim($this->editDocument),
            'first_name'     => mb_convert_case(mb_strtolower(trim($this->editFirstName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
            'last_name'      => mb_convert_case(mb_strtolower(trim($this->editLastName), 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
            'email'          => $this->editEmail ?: null,
            'student_code'   => $this->editStudentCode ?: null,
        ]);

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
        $this->editingId         = null;
        $this->editDocument      = '';
        $this->editFirstName     = '';
        $this->editLastName      = '';
        $this->editEmail         = '';
        $this->editStudentCode   = '';
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

        // Eliminar roles asociados
        $participant->activeRoles()->delete();
        \Illuminate\Support\Facades\DB::table('participant_roles')
            ->where('participant_id', $participant->id)
            ->delete();

        // Eliminar participante
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
