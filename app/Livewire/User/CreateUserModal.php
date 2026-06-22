<?php

namespace App\Livewire\User;

use App\Models\Dependency;
use App\Models\User;
use App\Services\ActivityLogService;
use Closure;
use Flux\Flux as FluxAlias;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateUserModal extends Component
{
    public $dependencies;

    public $campuses;

    public $roles;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public ?string $campus_id = null;

    public array $dependency_ids = [];

    public string $password = '';

    protected function rules()
    {
        $authUser = auth()->user();

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', 'string', Rule::in(array_keys($this->roles ?? []))],
            'campus_id' => [
                Rule::requiredIf(fn () => $this->role !== User::ROLE_SUPERADMIN),
                'nullable',
                'integer',
                'exists:campuses,id',
                function (string $attribute, mixed $value, Closure $fail) use ($authUser) {
                    $this->validateCampusAssignment($authUser, $this->role, $value, $fail);
                },
            ],
            'dependency_ids' => $this->role === User::ROLE_USER ? 'required|array|min:1' : 'nullable|array',
            'dependency_ids.*' => [
                'exists:dependencies,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    $this->validateDependencyCampus($this->role, $this->campus_id, $value, $fail);
                },
            ],
            'password' => 'required|string|min:8',
        ];
    }

    protected function messages()
    {
        return [
            'campus_id.required' => 'Selecciona una sede.',
            'dependency_ids.required' => 'Selecciona al menos una dependencia.',
            'dependency_ids.min' => 'Selecciona al menos una dependencia.',
        ];
    }

    public function mount(): void
    {
        if (auth()->user()?->isAdmin()) {
            $this->campus_id = (string) auth()->user()->campus_id;
        }
    }

    public function updatedRole(): void
    {
        if ($this->role === User::ROLE_SUPERADMIN) {
            $this->campus_id = null;
            $this->dependency_ids = [];

            return;
        }

        if ($this->role !== User::ROLE_USER) {
            $this->dependency_ids = [];
        }

        if (auth()->user()?->isAdmin()) {
            $this->campus_id = (string) auth()->user()->campus_id;
        }
    }

    public function updatedCampusId(): void
    {
        $this->discardDependenciesOutsideSelectedCampus();
    }

    public function getFilteredDependenciesProperty(): array
    {
        if (! $this->campus_id) {
            return [];
        }

        return Dependency::query()
            ->with('campus:id,name')
            ->where('campus_id', $this->campus_id)
            ->orderBy('name')
            ->get(['id', 'name', 'campus_id'])
            ->map(fn (Dependency $dependency) => [
                'id' => $dependency->id,
                'name' => $dependency->name.($dependency->campus?->name ? ' - '.$dependency->campus->name : ''),
            ])
            ->all();
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'campus_id' => $this->role === User::ROLE_SUPERADMIN ? null : (int) $this->campus_id,
            'password' => Hash::make($this->password),
        ]);

        if ($this->role === User::ROLE_USER && ! empty($this->dependency_ids)) {
            $user->dependencies()->attach($this->dependency_ids);
        }

        ActivityLogService::log('crear', 'usuarios', "Creo el usuario '{$user->name}'", $user);

        $this->reset(['name', 'email', 'role', 'campus_id', 'dependency_ids', 'password']);

        FluxAlias::modal('create-user-modal')->close();

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function render()
    {
        return view('livewire.user.create-user-modal');
    }

    private function validateCampusAssignment(?User $authUser, ?string $role, mixed $campusId, Closure $fail): void
    {
        if ($role === User::ROLE_SUPERADMIN) {
            if ($campusId !== null && $campusId !== '') {
                $fail('El superadministrador debe tener sede vacia.');
            }

            return;
        }

        if ($campusId === null || $campusId === '') {
            $fail('La sede es obligatoria para usuarios y administradores.');

            return;
        }

        if ($authUser?->isAdmin() && (int) $campusId !== (int) $authUser->campus_id) {
            $fail('No puedes asignar una sede diferente a la tuya.');
        }
    }

    private function validateDependencyCampus(?string $role, mixed $campusId, mixed $dependencyId, Closure $fail): void
    {
        if ($role !== User::ROLE_USER || ! $dependencyId || ! $campusId) {
            return;
        }

        $matchesCampus = \App\Models\Dependency::whereKey($dependencyId)
            ->where('campus_id', $campusId)
            ->exists();

        if (! $matchesCampus) {
            $fail('La dependencia seleccionada no pertenece a la sede indicada.');
        }
    }

    private function discardDependenciesOutsideSelectedCampus(): void
    {
        if (! $this->campus_id) {
            $this->dependency_ids = [];

            return;
        }

        $validIds = Dependency::where('campus_id', $this->campus_id)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->dependency_ids = array_values(array_filter(
            $this->dependency_ids,
            fn ($id) => in_array((string) $id, $validIds, true),
        ));
    }
}
