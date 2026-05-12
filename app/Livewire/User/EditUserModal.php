<?php

namespace App\Livewire\User;

use App\Models\User;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

class EditUserModal extends Component
{
    public $dependencies;
    public $roles;

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $role = '';
    public array $dependency_ids = [];
    public string $password = '';

    protected function rules()
    {
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $this->userId,
            'role'             => 'required|string',
            'dependency_ids'   => $this->role === 'user' ? 'required|array|min:1' : 'nullable|array',
            'dependency_ids.*' => 'exists:dependencies,id',
            'password'         => 'nullable|string|min:8',
        ];
    }

    protected function messages()
    {
        return [
            'dependency_ids.required' => 'Selecciona al menos una dependencia.',
            'dependency_ids.min'      => 'Selecciona al menos una dependencia.',
        ];
    }

    #[On('edit-user')]
    public function loadUser(int $id)
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->dependency_ids = $user->dependencies->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->password = '';
        $this->resetValidation();

        Flux::modal('edit-user-modal')->show();
    }

    public function updatedRole()
    {
        if ($this->role !== 'user') {
            $this->dependency_ids = [];
        }
    }

    public function save()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $original = $user->only(['name', 'email', 'role']);
        $originalDeps = $user->dependencies->pluck('id')->sort()->values()->toArray();

        $user->update([
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
        ]);

        if ($this->password) {
            $user->update(['password' => Hash::make($this->password)]);
        }

        $user->dependencies()->sync(
            $this->role === 'user' ? $this->dependency_ids : []
        );

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $user->$field;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }
        if ($this->password) {
            $changes['password'] = ['old' => '••••••••', 'new' => '••••••••(nueva)'];
        }
        $newDeps = collect($this->role === 'user' ? $this->dependency_ids : [])->map(fn($id) => (int) $id)->sort()->values()->toArray();
        if ($originalDeps !== $newDeps) {
            $changes['dependencias'] = ['old' => implode(', ', $originalDeps), 'new' => implode(', ', $newDeps)];
        }

        ActivityLogService::log('editar', 'usuarios', "Editó el usuario '{$user->name}'", $user, $changes);

        Flux::modal('edit-user-modal')->close();

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.user.edit-user-modal');
    }
}