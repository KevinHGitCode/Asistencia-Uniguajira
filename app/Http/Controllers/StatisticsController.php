<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Attendance;
use App\Models\Participant;
use App\Traits\AppliesStatisticsFilters;

class StatisticsController extends Controller
{
    use AppliesStatisticsFilters;

    /** ConcatenaciÃƒÂ³n compatible con SQLite (tests) y MySQL (producciÃƒÂ³n). */
    private function concatFullName(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "(participants.first_name || ' ' || participants.last_name)"
            : "CONCAT(participants.first_name, ' ', participants.last_name)";
    }

    // NÃƒÂºmero total de eventos
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
        $this->applyEventIds($query, $filters, 'id');

        return $query->count();
    }

    // NÃƒÂºmero de eventos por rol de usuario (eventos creados por cada rol: admin o user)
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

    // NÃƒÂºmero de eventos por usuario (eventos creados por cada usuario)
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

    // NÃƒÂºmero total de asistencias
    public function totalAttendances(Request $request)
    {
        $filters = $this->getFilters($request);
        $query = Attendance::query();

        $hasFilters = !empty($filters['dateFrom']) || !empty($filters['dateTo'])
            || (!empty($filters['userIds'])       && count($filters['userIds']) > 0)
            || (!empty($filters['dependencyIds']) && count($filters['dependencyIds']) > 0)
            || !empty($filters['eventIds']);

        if ($hasFilters) {
            $query->join('events', 'attendances.event_id', '=', 'events.id');

            if (!empty($filters['dateFrom'])) {
                $query->where('events.date', '>=', $filters['dateFrom']);
            }
            if (!empty($filters['dateTo'])) {
                $query->where('events.date', '<=', $filters['dateTo']);
            }
            if (!empty($filters['userIds']) && count($filters['userIds']) > 0) {
                $query->whereIn('events.user_id', $filters['userIds']);
            }
            if (!empty($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
                $query->join('users', 'events.user_id', '=', 'users.id')
                    ->whereIn('users.dependency_id', $filters['dependencyIds']);
            }
            $this->applyEventIds($query, $filters);
        }

        return $query->count();
    }

    // NÃƒÂºmero total de participantes (solo los que tienen al menos una asistencia)
    public function totalParticipants(Request $request)
    {
        $filters = $this->getFilters($request);

        $hasFilter = !empty($filters['dateFrom']) || !empty($filters['dateTo'])
            || !empty($filters['eventIds']);

        if ($hasFilter) {
            $query = DB::table('participants')
                ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
                ->join('events',      'attendances.event_id', '=', 'events.id');

            if (!empty($filters['dateFrom'])) {
                $query->where('events.date', '>=', $filters['dateFrom']);
            }
            if (!empty($filters['dateTo'])) {
                $query->where('events.date', '<=', $filters['dateTo']);
            }
            $this->applyEventIds($query, $filters);

            return $query->distinct()->count('participants.id');
        }

        // Sin filtros: solo los que tienen al menos una asistencia
        return Participant::whereHas('attendances')->count();
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
        if (!empty($filters['userIds']) && count($filters['userIds']) > 0) {
            $query->whereIn('events.user_id', $filters['userIds']);
        }
        if (!empty($filters['dependencyIds']) && count($filters['dependencyIds']) > 0) {
            $query->join('users', 'events.user_id', '=', 'users.id')
                ->whereIn('users.dependency_id', $filters['dependencyIds']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select('programs.name as program', DB::raw('COUNT(*) as count'))
            ->groupBy('programs.name')
            ->orderByDesc('count')
            ->get();
    }

    // Participantes por programa (solo los que han asistido al menos una vez, filtrable por fecha)
    public function participantsByProgram(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('participants')
            ->join('programs',    'participants.program_id', '=', 'programs.id')
            ->join('attendances', 'participants.id',         '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id',    '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select('programs.name as program', DB::raw('COUNT(DISTINCT participants.id) as count'))
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

    // Eventos con mÃƒÂ¡s asistencias
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
        $this->applyEventIds($query, $filters);

        return $query->select('events.title', DB::raw('COUNT(*) as count'))
            ->groupBy('events.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    // Participantes con mÃƒÂ¡s asistencias
    public function topParticipants(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events', 'attendances.event_id', '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select(DB::raw($this->concatFullName() . " as name"), DB::raw('COUNT(*) as count'))
            ->groupBy('participants.id', 'participants.first_name', 'participants.last_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ DemogrÃƒÂ¡ficos por ASISTENCIAS (cuentan registros, no personas ÃƒÂºnicas) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬

    // Asistencias por estamento del participante
    public function attendancesByRole(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select('participants.role as label', DB::raw('COUNT(*) as count'))
            ->groupBy('participants.role')
            ->orderByDesc('count')
            ->get();
    }

    // Asistencias por sexo del participante
    public function attendancesBySex(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select(
                DB::raw("COALESCE(participants.sexo, 'Sin datos') as label"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('participants.sexo')
            ->orderByDesc('count')
            ->get();
    }

    // Asistencias por grupo priorizado del participante
    public function attendancesByGroup(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select(
                DB::raw("COALESCE(participants.grupo_priorizado, 'Sin datos') as label"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('participants.grupo_priorizado')
            ->orderByDesc('count')
            ->get();
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ DemogrÃƒÂ¡ficos por PARTICIPANTES (cuentan personas ÃƒÂºnicas) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬

    // Participantes por estamento (rol: Estudiante / Docente)
    public function participantsByRole(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('participants')
            ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id', '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select('participants.role as label', DB::raw('COUNT(DISTINCT participants.id) as count'))
            ->groupBy('participants.role')
            ->orderByDesc('count')
            ->get();
    }

    // Participantes por sexo
    public function participantsBySex(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('participants')
            ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id', '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select(
                DB::raw("COALESCE(participants.sexo, 'Sin datos') as label"),
                DB::raw('COUNT(DISTINCT participants.id) as count')
            )
            ->groupBy('participants.sexo')
            ->orderByDesc('count')
            ->get();
    }

    // Participantes por grupo priorizado
    public function participantsByGroup(Request $request)
    {
        $filters = $this->getFilters($request);

        $query = DB::table('participants')
            ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id', '=', 'events.id');

        if (!empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        $this->applyEventIds($query, $filters);

        return $query->select(
                DB::raw("COALESCE(participants.grupo_priorizado, 'Sin datos') as label"),
                DB::raw('COUNT(DISTINCT participants.id) as count')
            )
            ->groupBy('participants.grupo_priorizado')
            ->orderByDesc('count')
            ->get();
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Endpoints de resumen (1 request = todos los datos del mÃƒÂ³dulo) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬

    /**
     * Retorna en una sola llamada todos los datos que necesita el mÃƒÂ³dulo
     * "Por Asistencias": contadores + grÃƒÂ¡ficos + demogrÃƒÂ¡ficos.
     */
    public function asistenciasSummary(Request $request)
    {
        $filters = $this->getFilters($request);

        $user = Auth::user();
        if ($user && $user->role !== 'admin') {
            $filters = $this->applyUserScope($filters, $user->id);
            if ($filters === null) {
                return response()->json([
                    'counters' => ['events' => 0, 'attendances' => 0, 'participants' => 0],
                    'charts'   => [
                        'attendancesByProgram' => [],
                        'topEvents'            => [],
                        'topParticipants'      => [],
                        'byRole'               => [],
                        'bySex'                => [],
                        'byGroup'              => [],
                    ],
                ]);
            }
        }

        return response()->json([
            'counters' => [
                'events'       => $this->sumTotalEvents($filters),
                'attendances'  => $this->sumTotalAttendances($filters),
                'participants' => $this->sumTotalParticipants($filters),
            ],
            'charts' => [
                'attendancesByProgram' => $this->sumAttendancesByProgram($filters),
                'topEvents'            => $this->sumTopEvents($filters),
                'topParticipants'      => $this->sumTopParticipants($filters),
                'byRole'  => $this->sumAttendancesDemoByField($filters, 'participants.role'),
                'bySex'   => $this->sumAttendancesDemoByField($filters, 'participants.sexo',            'Sin datos'),
                'byGroup' => $this->sumAttendancesDemoByField($filters, 'participants.grupo_priorizado', 'Sin datos'),
            ],
        ]);
    }

    /**
     * Retorna en una sola llamada todos los datos que necesita el mÃƒÂ³dulo
     * "Por Participantes": contadores + grÃƒÂ¡ficos + demogrÃƒÂ¡ficos.
     */
    public function participantesSummary(Request $request)
    {
        $filters = $this->getFilters($request);

        $user = Auth::user();
        if ($user && $user->role !== 'admin') {
            $filters = $this->applyUserScope($filters, $user->id);
            if ($filters === null) {
                return response()->json([
                    'counters' => ['events' => 0, 'participants' => 0],
                    'charts'   => [
                        'participantsByProgram' => [],
                        'byRole'               => [],
                        'bySex'                => [],
                        'byGroup'              => [],
                    ],
                ]);
            }
        }

        return response()->json([
            'counters' => [
                'events'       => $this->sumTotalEvents($filters),
                'participants' => $this->sumTotalParticipants($filters),
            ],
            'charts' => [
                'participantsByProgram' => $this->sumParticipantsByProgram($filters),
                'byRole'  => $this->sumParticipantsDemoByField($filters, 'participants.role'),
                'bySex'   => $this->sumParticipantsDemoByField($filters, 'participants.sexo',            'Sin datos'),
                'byGroup' => $this->sumParticipantsDemoByField($filters, 'participants.grupo_priorizado', 'Sin datos'),
            ],
        ]);
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Helpers privados para los endpoints de resumen Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬

    /**
     * Restringe los filtros al alcance del usuario: solo eventos creados por ÃƒÂ©l.
     * Retorna null si el usuario no tiene eventos (resultado debe ser ceros en todos los campos).
     */
    private function applyUserScope(array $filters, int $userId): ?array
    {
        $userEventIds = Event::where('user_id', $userId)->pluck('id')->toArray();

        if (empty($userEventIds)) {
            return null;
        }

        if (!empty($filters['eventIds'])) {
            $intersection = array_values(array_intersect($filters['eventIds'], $userEventIds));
            return empty($intersection) ? null : array_merge($filters, ['eventIds' => $intersection]);
        }

        return array_merge($filters, ['eventIds' => $userEventIds]);
    }

    private function sumTotalEvents(array $filters): int
    {
        $q = Event::query();
        if (!empty($filters['dateFrom'])) $q->where('date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('date', '<=', $filters['dateTo']);
        if (!empty($filters['userIds']))  $q->whereIn('user_id', $filters['userIds']);
        $this->applyEventIds($q, $filters, 'id');
        return $q->count();
    }

    private function sumTotalAttendances(array $filters): int
    {
        $hasFilters = !empty($filters['dateFrom']) || !empty($filters['dateTo']) || !empty($filters['eventIds']);
        $q = Attendance::query();
        if ($hasFilters) {
            $q->join('events', 'attendances.event_id', '=', 'events.id');
            if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
            if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
            $this->applyEventIds($q, $filters);
        }
        return $q->count();
    }

    private function sumTotalParticipants(array $filters): int
    {
        $hasFilter = !empty($filters['dateFrom']) || !empty($filters['dateTo']) || !empty($filters['eventIds']);
        if ($hasFilter) {
            $q = DB::table('participants')
                ->join('attendances', 'participants.id', '=', 'attendances.participant_id')
                ->join('events', 'attendances.event_id', '=', 'events.id');
            if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
            if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
            $this->applyEventIds($q, $filters);
            return $q->distinct()->count('participants.id');
        }
        return Participant::whereHas('attendances')->count();
    }

    private function sumAttendancesByProgram(array $filters)
    {
        $q = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('programs',     'participants.program_id',    '=', 'programs.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        return $q->select('programs.name as name', DB::raw('COUNT(*) as value'))
            ->groupBy('programs.name')->orderByDesc('value')->get();
    }

    private function sumTopEvents(array $filters)
    {
        $q = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        return $q->select('events.title as name', DB::raw('COUNT(*) as value'))
            ->groupBy('events.title')->orderByDesc('value')->limit(5)->get();
    }

    private function sumTopParticipants(array $filters)
    {
        $q = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        return $q->select(
                DB::raw($this->concatFullName() . ' as name'),
                DB::raw('COUNT(*) as value')
            )
            ->groupBy('participants.id', 'participants.first_name', 'participants.last_name')
            ->orderByDesc('value')->limit(5)->get();
    }

    private function sumAttendancesDemoByField(array $filters, string $col, ?string $coalesce = null)
    {
        $q = DB::table('attendances')
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->join('events',       'attendances.event_id',       '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        $expr = $coalesce ? DB::raw("COALESCE({$col}, '{$coalesce}') as name") : DB::raw("{$col} as name");
        return $q->select($expr, DB::raw('COUNT(*) as value'))
            ->groupBy($col)->orderByDesc('value')->get();
    }

    private function sumParticipantsByProgram(array $filters)
    {
        $q = DB::table('participants')
            ->join('programs',    'participants.program_id',    '=', 'programs.id')
            ->join('attendances', 'participants.id',            '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id',       '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        return $q->select('programs.name as name', DB::raw('COUNT(DISTINCT participants.id) as value'))
            ->groupBy('programs.name')->orderByDesc('value')->get();
    }

    private function sumParticipantsDemoByField(array $filters, string $col, ?string $coalesce = null)
    {
        $q = DB::table('participants')
            ->join('attendances', 'participants.id',         '=', 'attendances.participant_id')
            ->join('events',      'attendances.event_id',   '=', 'events.id');
        if (!empty($filters['dateFrom'])) $q->where('events.date', '>=', $filters['dateFrom']);
        if (!empty($filters['dateTo']))   $q->where('events.date', '<=', $filters['dateTo']);
        $this->applyEventIds($q, $filters);
        $expr = $coalesce ? DB::raw("COALESCE({$col}, '{$coalesce}') as name") : DB::raw("{$col} as name");
        return $q->select($expr, DB::raw('COUNT(DISTINCT participants.id) as value'))
            ->groupBy($col)->orderByDesc('value')->get();
    }

    // Usuarios con mÃƒÂ¡s asistencias
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
