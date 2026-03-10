<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Dependency;
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
    public function index(Request $request)
    {
        $query = Event::query()
            ->with(['user:id,name', 'dependency:id,name', 'area:id,name']);

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
            'id'              => $e->id,
            'title'           => $e->title,
            'description'     => $e->description,
            'date'            => $e->date,
            'start_time'      => $e->start_time,
            'end_time'        => $e->end_time,
            'location'        => $e->location,
            'user_name'       => $e->user?->name,
            'dependency_name' => $e->dependency?->name,
            'area_name'       => $e->area?->name,
        ]);

        return response()->json([
            'events' => $mapped->values(),
            'total'  => $mapped->count(),
        ]);
    }

    /**
     * Devuelve las opciones disponibles para los filtros:
     * lista de dependencias y usuarios con eventos.
     *
     * GET /api/statistics/admin-eventos/filter-options
     */
    public function filterOptions()
    {
        // Solo dependencias que tienen al menos un evento (JOIN es más rápido que whereHas)
        $dependencies = Dependency::select('dependencies.id', 'dependencies.name')
            ->join('events', 'dependencies.id', '=', 'events.dependency_id')
            ->distinct()
            ->orderBy('dependencies.name')
            ->get();

        // Solo usuarios que han creado al menos un evento (JOIN es más rápido que whereHas)
        $users = User::select('users.id', 'users.name')
            ->join('events', 'users.id', '=', 'events.user_id')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'dependencies' => $dependencies,
            'users'        => $users,
        ]);
    }
}