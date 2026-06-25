<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\UserActivityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request, UserActivityService $activity)
    {
        $authUser = $request->user();
        $search = trim((string) $request->query('q', ''));

        $users = User::select(['id', 'name', 'email', 'role', 'avatar', 'is_active', 'campus_id'])
            ->with(['campus:id,name', 'dependencies:id,name'])
            ->withCount(['dependencies', 'events'])
            ->when($authUser->isAdmin(), fn ($query) => $query->where('campus_id', $authUser->campus_id))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->paginate(20)
            ->withQueryString();

        $dependencies = $this->dependenciesFor($authUser);
        $campuses = $this->campusesFor($authUser);
        $roles = $this->rolesFor($authUser);
        $onlineUserIds = $activity->onlineUserIds();

        return view('users.index', compact('users', 'dependencies', 'campuses', 'roles', 'search', 'onlineUserIds'));
    }

    public function create(Request $request)
    {
        $authUser = $request->user();
        $dependencies = $this->dependenciesFor($authUser);
        $campuses = $this->campusesFor($authUser);
        $roles = $this->rolesFor($authUser);

        return view('users.create', compact('dependencies', 'campuses', 'roles'));
    }

    public function store(Request $request)
    {
        $authUser = $request->user();
        $allowedRoles = array_keys($this->rolesFor($authUser));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'campus_id' => [
                Rule::requiredIf(fn () => $request->input('role') !== User::ROLE_SUPERADMIN),
                'nullable',
                'integer',
                'exists:campuses,id',
                function (string $attribute, mixed $value, Closure $fail) use ($authUser, $request) {
                    $this->validateCampusAssignment($authUser, $request->input('role'), $value, $fail);
                },
            ],
            'dependency_id' => [
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_USER),
                'nullable',
                'integer',
                'exists:dependencies,id',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $this->validateDependencyCampus($request->input('role'), $request->input('campus_id'), $value, $fail);
                },
            ],
        ]);

        $role = $validated['role'];
        $campusId = $role === User::ROLE_SUPERADMIN ? null : (int) $validated['campus_id'];
        $dependencyId = $role === User::ROLE_USER ? ($validated['dependency_id'] ?? null) : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $role,
            'campus_id' => $campusId,
        ]);

        if ($dependencyId) {
            $user->dependencies()->attach($dependencyId);
        }

        ActivityLogService::log('crear', 'usuarios', "Creo el usuario '{$user->name}'", $user);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    public function show(string $id)
    {
        $user = $this->findManageableUser(request()->user(), $id);

        return view('users.show', compact('user'));
    }

    public function information(string $id, UserActivityService $activity)
    {
        $authUser = request()->user();
        $user = $this->findManageableUser($authUser, $id)->load(['dependencies']);
        $usage = $activity->usageFor($user);

        // Colecciones completas (no paginadas) para que el buscador del contenedor
        // <x-events.group> filtre sobre todo el conjunto, no solo una página (ADR-0012).
        $events = $user->events()
            ->with(['dependency', 'area', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $dependencyEvents = [];

        foreach ($user->dependencies as $dependency) {
            $dependencyEvents[$dependency->id] = [
                'dependency' => $dependency,
                'events' => Event::with(['dependency', 'area', 'user'])
                    ->where('dependency_id', $dependency->id)
                    ->where('user_id', '!=', $user->id)
                    ->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get(),
            ];
        }

        $eventsCount = $user->events()->count();
        $now = now()->toDateString();
        $upcomingEvents = $user->events()->where('date', '>=', $now)->count();
        $pastEvents = $user->events()->where('date', '<', $now)->count();

        return view('users.information', compact(
            'user',
            'events',
            'dependencyEvents',
            'eventsCount',
            'upcomingEvents',
            'pastEvents',
            'usage'
        ));
    }

    public function edit(string $id)
    {
        $authUser = request()->user();
        $user = $this->findManageableUser($authUser, $id);
        $dependencies = $this->dependenciesFor($authUser);
        $campuses = $this->campusesFor($authUser);
        $roles = $this->rolesFor($authUser, $user);

        return view('users.edit', compact('user', 'dependencies', 'campuses', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $authUser = $request->user();
        $user = $this->findManageableUser($authUser, $id);
        $requestedRole = $request->input('role', $user->role);
        $allowedRoles = array_keys($this->rolesFor($authUser, $user));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => ['sometimes', 'required', 'string', Rule::in($allowedRoles)],
            'campus_id' => [
                Rule::requiredIf(fn () => $requestedRole !== User::ROLE_SUPERADMIN),
                'nullable',
                'integer',
                'exists:campuses,id',
                function (string $attribute, mixed $value, Closure $fail) use ($authUser, $requestedRole) {
                    $this->validateCampusAssignment($authUser, $requestedRole, $value, $fail);
                },
            ],
            'dependency_id' => [
                Rule::requiredIf(fn () => $requestedRole === User::ROLE_USER),
                'nullable',
                'integer',
                'exists:dependencies,id',
                function (string $attribute, mixed $value, Closure $fail) use ($requestedRole, $request) {
                    $this->validateDependencyCampus($requestedRole, $request->input('campus_id'), $value, $fail);
                },
            ],
        ]);

        $role = $validated['role'] ?? $user->role;
        $campusId = $role === User::ROLE_SUPERADMIN ? null : (int) ($validated['campus_id'] ?? $user->campus_id);
        $dependencyId = $role === User::ROLE_USER ? ($validated['dependency_id'] ?? null) : null;

        $original = $user->only(['name', 'email', 'role', 'campus_id']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $role;
        $user->campus_id = $campusId;
        $user->save();

        $user->dependencies()->sync($dependencyId ? [$dependencyId] : []);

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $user->$field;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue ?? '-', 'new' => $newValue ?? '-'];
            }
        }

        ActivityLogService::log('editar', 'usuarios', "Edito el usuario '{$user->name}'", $user, $changes);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $authUser = Auth::user();
        $user = $this->findManageableUser($authUser, $id);
        $password = request('password');

        if (! Hash::check($password, $authUser->password)) {
            return redirect()->back()->withErrors(['password' => 'La contrasena es incorrecta.']);
        }

        $userName = $user->name;
        $user->delete();

        ActivityLogService::log('eliminar', 'usuarios', "Elimino el usuario '{$userName}'");

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
    }

    private function rolesFor(User $authUser, ?User $targetUser = null): array
    {
        if ($authUser->isSuperadmin()) {
            return [
                User::ROLE_USER => 'Usuario',
                User::ROLE_ADMIN => 'Administrador',
                User::ROLE_SUPERADMIN => 'Superadministrador',
            ];
        }

        if ($targetUser?->isAdmin()) {
            return [User::ROLE_ADMIN => 'Administrador'];
        }

        return [User::ROLE_USER => 'Usuario'];
    }

    private function campusesFor(User $authUser): array
    {
        return Campus::query()
            ->when($authUser->isAdmin(), fn ($query) => $query->whereKey($authUser->campus_id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function dependenciesFor(User $authUser): array
    {
        return Dependency::query()
            ->when($authUser->isAdmin(), fn ($query) => $query->where('campus_id', $authUser->campus_id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function validateCampusAssignment(User $authUser, ?string $role, mixed $campusId, Closure $fail): void
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

        if ($authUser->isAdmin() && (int) $campusId !== (int) $authUser->campus_id) {
            $fail('No puedes asignar una sede diferente a la tuya.');
        }
    }

    private function validateDependencyCampus(?string $role, mixed $campusId, mixed $dependencyId, Closure $fail): void
    {
        if ($role !== User::ROLE_USER || ! $dependencyId || ! $campusId) {
            return;
        }

        $matchesCampus = Dependency::whereKey($dependencyId)
            ->where('campus_id', $campusId)
            ->exists();

        if (! $matchesCampus) {
            $fail('La dependencia seleccionada no pertenece a la sede indicada.');
        }
    }

    private function findManageableUser(User $authUser, string $id): User
    {
        $query = User::query();

        if ($authUser->isAdmin()) {
            $query->where('campus_id', $authUser->campus_id);
        }

        return $query->findOrFail($id);
    }
}
