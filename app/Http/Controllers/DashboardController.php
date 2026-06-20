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

        if ($user->hasAdminAccess()) {
            $eventQuery = $campusScope->applyToQuery(Event::query(), $user);
            $eventosCount = (clone $eventQuery)->count();

            $asistenciasCount = Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
            })->count();

            $participantesCount = Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
            })->distinct('participant_id')->count('participant_id');
        } else {
            $eventosCount = $campusScope->applyToQuery(
                Event::where('user_id', $user->id),
                $user
            )->count();

            $asistenciasCount = Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
                $query->where('user_id', $user->id);
            })->count();

            $participantesCount = Attendance::whereHas('event', function ($query) use ($campusScope, $user) {
                $campusScope->applyToQuery($query, $user);
                $query->where('user_id', $user->id);
            })->distinct('participant_id')->count('participant_id');
        }

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

        if (empty($validated['campus_id'])) {
            $request->session()->forget(CampusScopeService::SESSION_KEY);
        } else {
            $request->session()->put(CampusScopeService::SESSION_KEY, (int) $validated['campus_id']);
        }

        return redirect()->route('dashboard');
    }
}
