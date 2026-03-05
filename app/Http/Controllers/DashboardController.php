<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Dependency;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $username = ucfirst(strtolower($user->name));

        if ($user->role === 'admin') {
            // Super Admin → Totales generales
            $eventosCount = Event::count();
            $asistenciasCount = Attendance::count();
            // Solo participantes que tienen al menos una asistencia registrada
            $participantesCount = Participant::whereHas('attendances')->count();
        } else {
            // Usuario (organizador) → solo sus eventos
            $eventosCount = Event::where('user_id', $user->id)->count();

            $asistenciasCount = Attendance::whereHas('event', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();

            $participantesCount = Attendance::whereHas('event', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->distinct('participant_id')->count('participant_id');
        }

        $dependencies = Dependency::orderBy('name')->pluck('name', 'id')->toArray();
        $roles = ['admin' => 'Administrador', 'user' => 'Usuario'];

        return view('dashboard', compact('username', 'eventosCount', 'asistenciasCount', 'participantesCount', 'dependencies', 'roles'));
    }
}
