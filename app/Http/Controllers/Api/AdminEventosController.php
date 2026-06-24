<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;

class AdminEventosController extends Controller
{
    /**
     * Devuelve todos los eventos del sistema con filtros opcionales.
     *
     * GET /api/statistics/admin-eventos
     *   ?from=2025-01-01
     *   &to=2025-12-31
     *   &search=nombre+evento
     *   &dependencies[]=1&dependencies[]=2
     *   &users[]=3&users[]=5
     */
    public function index(Request $request, CampusScopeService $campusScope)
    {
        $query = Event::query()
            ->with(['user:id,name', 'dependency:id,name', 'area:id,name']);

        $selectedCampusId = $this->applyModuleCampusScope($query, $request, $campusScope);

        // ── Filtro de fecha ──
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // ── Filtro por nombre del evento ──
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        // ── Filtro por dependencias (array de IDs) ──
        if ($request->filled('dependencies')) {
            $deps = (array) $request->dependencies;
            $query->whereIn('dependency_id', $deps);
        }

        // ── Filtro por usuarios creadores (array de IDs) ──
        if ($request->filled('users')) {
            $users = (array) $request->users;
            $query->whereIn('user_id', $users);
        }

        $events = $query->orderByDesc('date')
            ->orderByDesc('start_time')
            ->limit(500)
            ->get();

        $mapped = $events->map(fn ($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'date' => $e->date,
            'start_time' => $e->start_time,
            'end_time' => $e->end_time,
            'location' => $e->location,
            'user_name' => $e->user?->name,
            'dependency_name' => $e->dependency?->name,
            'area_name' => $e->area?->name,
        ]);

        return response()->json([
            'events' => $mapped->values(),
            'total' => $mapped->count(),
            'selected_campus_id' => $selectedCampusId,
        ]);
    }

    /**
     * Devuelve las opciones disponibles para los filtros:
     * lista de dependencias y usuarios con eventos.
     *
     * GET /api/statistics/admin-eventos/filter-options
     */
    public function filterOptions(Request $request, CampusScopeService $campusScope)
    {
        $user = $request->user();
        $selectedCampusId = $this->selectedCampusId($request);

        // Solo dependencias que tienen al menos un evento (JOIN es más rápido que whereHas)
        $dependencies = Dependency::select('dependencies.id', 'dependencies.name')
            ->join('events', 'dependencies.id', '=', 'events.dependency_id')
            ->distinct()
            ->orderBy('dependencies.name');

        if ($user?->isSuperadmin()) {
            if ($selectedCampusId !== null) {
                $dependencies->where('events.campus_id', $selectedCampusId);
            }
        } else {
            $campusScope->applyToQuery($dependencies, $user, 'events.campus_id');
        }

        $dependencies = $dependencies->get();

        // Solo usuarios que han creado al menos un evento (JOIN es más rápido que whereHas)
        $users = User::select('users.id', 'users.name')
            ->join('events', 'users.id', '=', 'events.user_id')
            ->distinct()
            ->orderBy('users.name');

        if ($user?->isSuperadmin()) {
            if ($selectedCampusId !== null) {
                $users->where('events.campus_id', $selectedCampusId);
            }
        } else {
            $campusScope->applyToQuery($users, $user, 'events.campus_id');
        }

        $users = $users->get();

        return response()->json([
            'show_campuses' => (bool) $user?->isSuperadmin(),
            'selected_campus_id' => $selectedCampusId,
            'campuses' => $user?->isSuperadmin()
                ? Campus::orderBy('name')->get(['id', 'name'])
                : [],
            'dependencies' => $dependencies,
            'users' => $users,
        ]);
    }

    private function selectedCampusId(Request $request): ?int
    {
        if (! $request->user()?->isSuperadmin() || ! $request->query->has('campus_id')) {
            return null;
        }

        $validated = $request->validate([
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
        ]);

        return empty($validated['campus_id']) ? null : (int) $validated['campus_id'];
    }

    private function applyModuleCampusScope($query, Request $request, CampusScopeService $campusScope): ?int
    {
        $user = $request->user();
        $selectedCampusId = $this->selectedCampusId($request);

        if ($user?->isSuperadmin()) {
            if ($selectedCampusId !== null) {
                $query->where('campus_id', $selectedCampusId);
            }

            return $selectedCampusId;
        }

        $campusScope->applyToQuery($query, $user);

        return null;
    }
}
