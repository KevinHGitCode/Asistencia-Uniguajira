<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(CampusScopeService $campusScope)
    {
        $user = Auth::user();
        $username = ucfirst(strtolower($user->name));
        $stats = $this->statsForUser($user, $campusScope);
        $eventosCount = $stats['eventos'];
        $asistenciasCount = $stats['asistencias'];
        $participantesCount = $stats['participantes'];

        $dependencies = $campusScope->applyToQuery(Dependency::query(), $user)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        $roles = ['user' => 'Usuario', 'admin' => 'Administrador', 'superadmin' => 'Superadministrador'];
        $campuses = Campus::orderBy('name')->pluck('name', 'id')->toArray();
        $activeCampusId = $campusScope->activeCampusId($user);

        return view('dashboard', compact(
            'username',
            'eventosCount',
            'asistenciasCount',
            'participantesCount',
            'dependencies',
            'roles',
            'campuses',
            'activeCampusId'
        ));
    }

    public function updateCampus(Request $request)
    {
        abort_unless($request->user()?->isSuperadmin(), 403);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
        ]);

        $campusId = empty($validated['campus_id']) ? null : (int) $validated['campus_id'];

        if ($campusId === null) {
            $request->session()->forget(CampusScopeService::SESSION_KEY);
        } else {
            $request->session()->put(CampusScopeService::SESSION_KEY, $campusId);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'campus_id' => $campusId,
                'stats' => $this->statsForUser($request->user(), app(CampusScopeService::class)),
            ]);
        }

        return redirect()->route('dashboard');
    }

    private function statsForUser($user, CampusScopeService $campusScope): array
    {
        if ($user->hasAdminAccess()) {
            $eventQuery = $campusScope->applyToQuery(Event::query(), $user);

            return [
                'eventos' => (clone $eventQuery)->count(),
                'asistencias' => Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                    $campusScope->applyToQuery($query, $user);
                })->count(),
                'participantes' => Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                    $campusScope->applyToQuery($query, $user);
                })->distinct('participant_id')->count('participant_id'),
            ];
        }

        return [
            'eventos' => $campusScope->applyToQuery(
                Event::where('user_id', $user->id),
                $user
            )->count(),
            'asistencias' => Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
                $query->where('user_id', $user->id);
            })->count(),
            'participantes' => Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
                $query->where('user_id', $user->id);
            })->distinct('participant_id')->count('participant_id'),
        ];
    }
}
