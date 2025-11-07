<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Participant;

class StatisticsController extends Controller
{
    // Número total de eventos
    public function totalEvents()
    {
        return Event::count();
    }

    // Número de eventos por rol de usuario (eventos creados por cada rol: admin o user)
    public function eventsByRole()
    {
        return DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.role', DB::raw('COUNT(*) as count'))
            ->groupBy('users.role')
            ->orderByDesc('count')
            ->get();
    }

    // Número de eventos por usuario (eventos creados por cada usuario)
    public function eventsByUser()
    {
        return DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->get();
    }

    // Número total de asistencias
    public function totalAttendances()
    {
        return Attendance::count();
    }

    // Número total de participantes
    public function totalParticipants()
    {
        return Participant::count();
    }

    // Asistencias por programa
    public function attendancesByProgram()
    {
        return DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('programs', 'participants.program_id', '=', 'programs.id')
            ->select('programs.name as program', DB::raw('COUNT(*) as count'))
            ->groupBy('programs.name')
            ->orderByDesc('count')
            ->get();
    }

    // Participantes por programa (solo los que han asistido al menos una vez)
    public function participantsByProgram()
    {
        return Participant::join('programs', 'participants.program_id', '=', 'programs.id')
            ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
            ->select('programs.name as program', DB::raw('COUNT(DISTINCT participants.id) as count'))
            ->groupBy('programs.name')
            ->orderByDesc('count')
            ->get();
    }

    // Eventos vs tiempo
    public function eventsOverTime()
    {
        return Event::select(DB::raw('DATE(date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Asistencias vs tiempo
    public function attendancesOverTime()
    {
        return DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->select(DB::raw('DATE(events.date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Eventos con más asistencias
    public function topEvents()
    {
        return DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->select('events.title', DB::raw('COUNT(*) as count'))
            ->groupBy('events.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    // Participantes con más asistencias
    public function topParticipants()
    {
        return DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->select(DB::raw("CONCAT(participants.first_name, ' ', participants.last_name) as name"), DB::raw('COUNT(*) as count'))
            ->groupBy('participants.id', 'participants.first_name', 'participants.last_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    // Usuarios con más asistencias
    public function topUsers()
    {
        return DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }
}
