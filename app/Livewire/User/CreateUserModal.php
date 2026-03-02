<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Flux;
use Flux\Flux as FluxAlias;

class CreateUserModal extends Component
{
    public $dependencies;
    public $roles;

    public string $name = '';
    public string $email = '';
    public string $role = '';
    public array $dependency_ids = [];
    public string $password = '';

    protected function rules()
    {
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'role'             => 'required|string',
            'dependency_ids'   => $this->role === 'user' ? 'required|array|min:1' : 'nullable|array',
            'dependency_ids.*' => 'exists:dependencies,id',
            'password'         => 'required|string|min:8',
        ];
    }

    protected function messages()
    {
        return [
            'dependency_ids.required' => 'Selecciona al menos una dependencia.',
            'dependency_ids.min'      => 'Selecciona al menos una dependencia.',
        ];
    }

    public function updatedRole()
    {
        // Limpiar dependencias al cambiar de rol
        if ($this->role !== 'user') {
            $this->dependency_ids = [];
        }
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'role'     => $this->role,
            'password' => Hash::make($this->password),
        ]);

        // Asociar dependencias si es usuario
        if ($this->role === 'user' && !empty($this->dependency_ids)) {
            $user->dependencies()->attach($this->dependency_ids);
        }

        $this->reset(['name', 'email', 'role', 'dependency_ids', 'password']);

        FluxAlias::modal('create-user-modal')->close();

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function render()
    {
        return view('livewire.user.create-user-modal');
    }
}