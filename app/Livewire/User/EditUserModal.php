<?php

namespace App\Livewire\User;

use App\Models\Dependency;
use App\Models\User;
use App\Services\ActivityLogService;
use Closure;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditUserModal extends Component
{
    public $dependencies;

    public $campuses;

    public $roles;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public ?string $campus_id = null;

    public array $dependency_ids = [];

    public string $password = '';

    /** '1' = activo · '0' = inactivo (solo se aplica al guardar si canToggleActive). */
    public string $activeState = '1';

    /** Si el usuario autenticado puede activar/desactivar al usuario en edición (jerarquía). */
    public bool $canToggleActive = false;

    protected function rules()
    {
        $authUser = auth()->user();

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->userId,
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
            'password' => 'nullable|string|min:8',
            'activeState' => ['nullable', Rule::in(['0', '1'])],
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

    #[On('edit-user')]
    public function loadUser(int $id): void
    {
        $query = User::query()->with('dependencies');
        $authUser = auth()->user();

        abort_unless($authUser?->hasAdminAccess(), 403);

        if ($authUser?->isAdmin()) {
            $query->where('campus_id', $authUser->campus_id);
        }

        $user = $query->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->roles = $this->rolesFor($authUser, $user);
        $this->campus_id = $user->campus_id ? (string) $user->campus_id : null;
        $this->dependency_ids = $user->dependencies->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->password = '';
        $this->activeState = $user->is_active ? '1' : '0';
        $this->canToggleActive = $this->canToggle($authUser, $user);
        $this->resetValidation();

        Flux::modal('edit-user-modal')->show();
    }

    /**
     * Jerarquía para activar/desactivar usuarios:
     * - Superadmin: puede gestionar a cualquiera (usuarios, administradores y otros superadmin).
     * - Admin: solo a usuarios normales de su propia sede.
     * - Nadie puede cambiar su propio estado.
     */
    private function canToggle(?User $authUser, User $target): bool
    {
        if (! $authUser || $authUser->id === $target->id) {
            return false;
        }

        if ($authUser->isSuperadmin()) {
            return true;
        }

        if ($authUser->isAdmin()) {
            return $target->role === User::ROLE_USER
                && (int) $target->campus_id === (int) $authUser->campus_id;
        }

        return false;
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
                'name' => $dependency->campus
                    ? "{$dependency->name} - {$dependency->campus->name}"
                    : $dependency->name,
            ])
            ->all();
    }

    public function save()
    {
        $this->validate();

        $query = User::query();
        $authUser = auth()->user();

        abort_unless($authUser?->hasAdminAccess(), 403);

        if ($authUser?->isAdmin()) {
            $query->where('campus_id', $authUser->campus_id);
        }

        $user = $query->findOrFail($this->userId);

        $original = $user->only(['name', 'email', 'role', 'campus_id']);
        $originalActive = (bool) $user->is_active;
        $originalDeps = $user->dependencies->pluck('id')->sort()->values()->toArray();

        // El estado solo cambia si la jerarquía lo permite (defensa del lado servidor).
        $canToggle = $this->canToggle($authUser, $user);

        $user->update(array_merge([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'campus_id' => $this->role === User::ROLE_SUPERADMIN ? null : (int) $this->campus_id,
        ], $canToggle ? ['is_active' => $this->activeState === '1'] : []));

        if ($this->password) {
            $user->update(['password' => Hash::make($this->password)]);
        }

        $user->dependencies()->sync(
            $this->role === User::ROLE_USER ? $this->dependency_ids : []
        );

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $user->$field;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue ?? '-', 'new' => $newValue ?? '-'];
            }
        }

        if ($this->password) {
            $changes['password'] = ['old' => '********', 'new' => '********(nueva)'];
        }

        $newDeps = collect($this->role === User::ROLE_USER ? $this->dependency_ids : [])->map(fn ($id) => (int) $id)->sort()->values()->toArray();
        if ($originalDeps !== $newDeps) {
            $changes['dependencias'] = ['old' => implode(', ', $originalDeps), 'new' => implode(', ', $newDeps)];
        }

        if ($canToggle && $originalActive !== ($this->activeState === '1')) {
            $changes['estado'] = [
                'old' => $originalActive ? 'activo' : 'inactivo',
                'new' => $this->activeState === '1' ? 'activo' : 'inactivo',
            ];
        }

        ActivityLogService::log('editar', 'usuarios', "Edito el usuario '{$user->name}'", $user, $changes);

        Flux::modal('edit-user-modal')->close();

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.user.edit-user-modal');
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

    private function rolesFor(?User $authUser, User $targetUser): array
    {
        if ($authUser?->isSuperadmin()) {
            return [
                User::ROLE_USER => 'Usuario',
                User::ROLE_ADMIN => 'Administrador',
                User::ROLE_SUPERADMIN => 'Superadministrador',
            ];
        }

        if ($targetUser->isAdmin()) {
            return [User::ROLE_ADMIN => 'Administrador'];
        }

        return [User::ROLE_USER => 'Usuario'];
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
