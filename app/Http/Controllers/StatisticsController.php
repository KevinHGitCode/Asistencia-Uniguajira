<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Participant;
use App\Traits\AppliesStatisticsFilters;

class StatisticsController extends Controller
{
    use AppliesStatisticsFilters;

    // Número total de eventos
    public function totalEvents(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = Event::query();
        
        if (!empty($filters['dateFrom'])) {
            $query->where('date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds'])) {
            $query->whereIn('user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds'])) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->whereIn('dependency_id', $filters['dependencyIds']);
            });
        }
        
        return $query->count();
    }

    // Número de eventos por rol de usuario (eventos creados por cada rol: admin o user)
    public function eventsByRole(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds'])) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds'])) {
            $query->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select('users.role', DB::raw('COUNT(*) as count'))
            ->groupBy('users.role')
            ->orderByDesc('count')
            ->get();
    }

    // Número de eventos por usuario (eventos creados por cada usuario)
    public function eventsByUser(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('events')
            ->join('users', 'events.user_id', '=', 'users.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds'])) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds'])) {
            $query->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select('users.name', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->get();
    }

    // Número total de asistencias
    public function totalAttendances(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = Attendance::query();
        
        $hasFilters = !empty($filters['dateFrom']) || !empty($filters['dateTo']) 
            || (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0)
            || (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0);
        
        if ($hasFilters) {
            $query->join('events', 'attendances.event_id', '=', 'events.id');
            
            if (!empty($filters['dateFrom'])) {
                $query->where('events.date', '>=', $filters['dateFrom']);
            }
            if (!empty($filters['dateTo'])) {
                $query->where('events.date', '<=', $filters['dateTo']);
            }
            if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
                $query->whereIn('events.user_id', $filters['userIds']);
            }
            if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
                $query->join('users', 'events.user_id', '=', 'users.id')
                    ->whereIn('users.dependency_id', $filters['dependencyIds']);
            }
        }
        
        return $query->count();
    }

    // Número total de participantes
    public function totalParticipants()
    {
        return Participant::count();
    }

    // Asistencias por programa
    public function attendancesByProgram(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('programs', 'participants.program_id', '=', 'programs.id')
            ->join('events', 'attendances.event_id', '=', 'events.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->join('users', 'events.user_id', '=', 'users.id')
                ->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select('programs.name as program', DB::raw('COUNT(*) as count'))
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
    public function eventsOverTime(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = Event::query();
        
        if (!empty($filters['dateFrom'])) {
            $query->where('date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->whereHas('user', function($q) use ($filters) {
                $q->whereIn('dependency_id', $filters['dependencyIds']);
            });
        }
        
        return $query->select(DB::raw('DATE(date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Asistencias vs tiempo
    public function attendancesOverTime(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->join('users', 'events.user_id', '=', 'users.id')
                ->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select(DB::raw('DATE(events.date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Eventos con más asistencias
    public function topEvents(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->join('users', 'events.user_id', '=', 'users.id')
                ->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select('events.title', DB::raw('COUNT(*) as count'))
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
    public function topUsers(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->join('users', 'events.user_id', '=', 'users.id');
        
        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['userIds']) && is_array($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && is_array($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        
        return $query->select('users.name', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }
}
