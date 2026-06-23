<?php

use App\Http\Controllers\Api\AdminEventosController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StatisticsController;
use App\Models\Event;
use App\Models\User;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * ================================================================
 *  RUTAS PARA LA GESTION DEL CALENDARIO
 * ================================================================
 */
$applyCalendarVisibility = function ($query, User $user, CampusScopeService $campusScope): void {
    $campusScope->applyToQuery($query, $user);

    if ($user->hasAdminAccess()) {
        return;
    }

    $user->loadMissing('dependencies');
    $dependencyIds = $user->dependencies->pluck('id')->all();

    $query->where(function ($eventQuery) use ($user, $dependencyIds) {
        $eventQuery->where('user_id', $user->id);

        if ($dependencyIds !== []) {
            $eventQuery->orWhereIn('dependency_id', $dependencyIds);
        }
    });
};

$applyStatisticsEventVisibility = function ($query, User $user, CampusScopeService $campusScope): void {
    $campusScope->applyToQuery($query, $user, 'events.campus_id');

    if ($user->hasAdminAccess()) {
        return;
    }

    $user->loadMissing('dependencies');
    $dependencyIds = $user->dependencies->pluck('id')->all();

    $query->where(function ($eventQuery) use ($user, $dependencyIds) {
        $eventQuery->where('events.user_id', $user->id);

        if ($dependencyIds !== []) {
            $eventQuery->orWhereIn('events.dependency_id', $dependencyIds);
        }
    });
};

$authorizeStatisticsEvent = function (int $eventId, CampusScopeService $campusScope) use ($applyStatisticsEventVisibility): int {
    $user = Auth::user();
    $query = DB::table('events')->where('events.id', $eventId);

    $applyStatisticsEventVisibility($query, $user, $campusScope);

    abort_unless($query->exists(), 403);

    return $eventId;
};

// Ruta para obtener eventos en formato JSON para el calendario
Route::middleware(['web', 'auth'])->get('/eventos-json', function (CampusScopeService $campusScope) use ($applyCalendarVisibility) {
    $user = Auth::user();
    $now = now();
    $year = $now->year;
    if ($now->month >= 1 && $now->month <= 6) {
        // Primer semestre: 1 de enero a 30 de junio
        $start = "$year-01-01";
        $end = "$year-06-30";
    } else {
        // Segundo semestre: 1 de julio a 31 de diciembre
        $start = "$year-07-01";
        $end = "$year-12-31";
    }
    $query = Event::query()
        ->selectRaw('DATE(date) as date, COUNT(*) as count')
        ->whereBetween('date', [$start, $end]);

    $applyCalendarVisibility($query, $user, $campusScope);

    return $query
        ->groupBy('date')
        ->get();
});

// Ruta para obtener eventos por fecha específica
Route::middleware(['web', 'auth'])->get('/events/{date}', [EventController::class, 'getByDate']);

// Ruta para obtener eventos del usuario autenticado en formato JSON para el calendario
Route::middleware(['web', 'auth'])->get('/mis-eventos-json', function (CampusScopeService $campusScope) {
    $user = Auth::user();
    $now = now();
    $year = $now->year;

    if ($now->month <= 6) {
        $start = "$year-01-01";
        $end = "$year-06-30";
    } else {
        $start = "$year-07-01";
        $end = "$year-12-31";
    }

    return response()->json([
        'auth_id' => Auth::id(),
        'eventos' => $campusScope->applyToQuery(
            Event::selectRaw('DATE(date) as date, COUNT(*) as count')
                ->where('user_id', Auth::id())
                ->whereBetween('date', [$start, $end]),
            $user
        )
            ->groupBy('date')
            ->get(),
    ]);
});

/**
 * =============================================
 * RUTAS PARA LAS ESTADÍSTICAS ESPECÍFICAS DEL EVENTO
 * =============================================
 */

// Ruta para obtener datos de programas específicos del evento
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/programs', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_roles', 'attendance_details.participant_role_id', '=', 'participant_roles.id')
        ->join('programs', 'participant_roles.program_id', '=', 'programs.id')
        ->select('programs.name as program', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('programs.name')
        ->orderByDesc('count')
        ->get();
});

// Ruta para obtener estamentos específicos del evento
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/roles', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_roles', 'attendance_details.participant_role_id', '=', 'participant_roles.id')
        ->join('participant_types', 'participant_roles.participant_type_id', '=', 'participant_types.id')
        ->select('participant_types.name as role', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('participant_types.name')
        ->orderByDesc('count')
        ->get();
});

// Distribución por género de un evento específico
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/sex', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->select(
            DB::raw("COALESCE(attendance_details.gender, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->where('attendances.event_id', $event)
        ->groupBy('attendance_details.gender')
        ->orderByDesc('count')
        ->get();
});

// Distribución por grupo priorizado de un evento específico
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/group', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->select(
            DB::raw("COALESCE(attendance_details.priority_group, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->where('attendances.event_id', $event)
        ->groupBy('attendance_details.priority_group')
        ->orderByDesc('count')
        ->get();
});

// Distribución por dependencia de un evento específico
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/dependencies', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_roles', 'attendance_details.participant_role_id', '=', 'participant_roles.id')
        ->join('dependencies', 'participant_roles.dependency_id', '=', 'dependencies.id')
        ->select('dependencies.name as label', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('dependencies.id', 'dependencies.name')
        ->orderByDesc('count')
        ->get();
});

// Distribución por organización de un evento específico
Route::middleware(['web', 'auth'])->get('/statistics/event/{event}/organizations', function ($event, CampusScopeService $campusScope) use ($authorizeStatisticsEvent) {
    $event = $authorizeStatisticsEvent((int) $event, $campusScope);

    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_roles', 'attendance_details.participant_role_id', '=', 'participant_roles.id')
        ->join('organizations', 'participant_roles.organization_id', '=', 'organizations.id')
        ->select('organizations.name as label', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('organizations.id', 'organizations.name')
        ->orderByDesc('count')
        ->get();
});

/**
 * =============================================
 * RUTAS PARA COMPARAR EVENTOS
 * =============================================
 */

// Lista de eventos disponibles para comparar (admin ve todos; usuario ve solo los suyos)
Route::middleware(['web', 'auth', 'throttle:api-stats'])->get('/statistics/compare/events', function (Request $request, CampusScopeService $campusScope) use ($applyStatisticsEventVisibility) {
    $user = Auth::user();

    $query = DB::table('events')
        ->leftJoin('attendances', 'events.id', '=', 'attendances.event_id')
        ->select(
            'events.id',
            'events.title',
            DB::raw('DATE(events.date) as date'),
            DB::raw('COUNT(attendances.id) as attendances_count')
        )
        ->groupBy('events.id', 'events.title', 'events.date')
        ->orderByDesc('events.date');

    $applyStatisticsEventVisibility($query, $user, $campusScope);

    $dateFrom = $request->get('dateFrom');
    $dateTo = $request->get('dateTo');
    if ($dateFrom) {
        $query->where('events.date', '>=', $dateFrom);
    }
    if ($dateTo) {
        $query->where('events.date', '<=', $dateTo);
    }

    return $query->get();
});

// Datos comparativos para los eventos seleccionados (asistencias + demografía)
Route::middleware(['web', 'auth', 'throttle:api-stats'])->get('/statistics/compare/data', function (Request $request, CampusScopeService $campusScope) use ($applyStatisticsEventVisibility) {
    $eventIds = array_values(array_filter(array_map('intval', (array) $request->get('eventIds', []))));

    if (empty($eventIds)) {
        return ['attendances' => [], 'byRole' => [], 'bySex' => [], 'byGroup' => []];
    }

    $allowedEventsQuery = DB::table('events')
        ->whereIn('events.id', $eventIds)
        ->select('events.id');

    $applyStatisticsEventVisibility($allowedEventsQuery, Auth::user(), $campusScope);

    $eventIds = $allowedEventsQuery->pluck('events.id')->all();

    if (empty($eventIds)) {
        return ['attendances' => [], 'byRole' => [], 'bySex' => [], 'byGroup' => []];
    }

    $attendances = DB::table('events')
        ->leftJoin('attendances', 'events.id', '=', 'attendances.event_id')
        ->select(
            'events.id',
            'events.title',
            DB::raw('DATE(events.date) as date'),
            DB::raw('COUNT(attendances.id) as count')
        )
        ->whereIn('events.id', $eventIds)
        ->groupBy('events.id', 'events.title', 'events.date')
        ->orderBy('events.date')
        ->get();

    // Demografía desde attendance_details (datos capturados en el momento del registro)
    $demoDetail = fn (string $col) => DB::table('attendances')
        ->join('events', 'attendances.event_id', '=', 'events.id')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->select(
            'events.id as event_id',
            'events.title as event_title',
            DB::raw("COALESCE(attendance_details.{$col}, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->whereIn('attendances.event_id', $eventIds)
        ->groupBy('events.id', 'events.title', "attendance_details.{$col}")
        ->orderBy('events.date')
        ->get();

    $byRole = DB::table('attendances')
        ->join('events', 'attendances.event_id', '=', 'events.id')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_roles', 'attendance_details.participant_role_id', '=', 'participant_roles.id')
        ->join('participant_types', 'participant_roles.participant_type_id', '=', 'participant_types.id')
        ->select(
            'events.id as event_id',
            'events.title as event_title',
            'participant_types.name as label',
            DB::raw('COUNT(*) as count')
        )
        ->whereIn('attendances.event_id', $eventIds)
        ->groupBy('events.id', 'events.title', 'participant_types.id', 'participant_types.name')
        ->orderBy('events.date')
        ->get();

    return [
        'attendances' => $attendances,
        'byRole' => $byRole,
        'bySex' => $demoDetail('gender'),
        'byGroup' => $demoDetail('priority_group'),
    ];
});

// ✅ Usa sesión web + auth, igual que las demás rutas
Route::middleware(['web', 'auth', 'role:admin,superadmin', 'throttle:api-stats'])->group(function () {
    Route::get('/statistics/admin-eventos', [AdminEventosController::class, 'index']);
    Route::get('/statistics/admin-eventos/filter-options', [AdminEventosController::class, 'filterOptions']);
});

/**
 * =============================================
 * RUTAS PARA LAS ESTADÍSTICAS GENERALES
 * =============================================
 */

// ── Endpoints de resumen: requieren sesión para filtrar por rol de usuario ──
Route::middleware(['web', 'auth', 'throttle:api-stats'])->prefix('statistics')->controller(StatisticsController::class)->group(function () {
    Route::get('/asistencias-summary', 'asistenciasSummary');
    Route::get('/participantes-summary', 'participantesSummary');
});

Route::middleware(['web', 'auth', 'throttle:api-stats'])->prefix('statistics')->controller(StatisticsController::class)->group(function () {
    Route::get('/total-events', 'totalEvents');
    Route::get('/events-by-role', 'eventsByRole');
    Route::get('/events-by-user', 'eventsByUser');
    Route::get('/total-attendances', 'totalAttendances');
    Route::get('/total-participants', 'totalParticipants');
    Route::get('/attendances-by-program', 'attendancesByProgram');
    Route::get('/participants-by-program', 'participantsByProgram');
    Route::get('/events-over-time', 'eventsOverTime');
    Route::get('/attendances-over-time', 'attendancesOverTime');
    Route::get('/top-events', 'topEvents');
    Route::get('/top-participants', 'topParticipants');
    Route::get('/top-users', 'topUsers');
    Route::get('/attendances-by-role', 'attendancesByRole');
    Route::get('/attendances-by-sex', 'attendancesBySex');
    Route::get('/attendances-by-group', 'attendancesByGroup');
    Route::get('/participants-by-role', 'participantsByRole');
    Route::get('/participants-by-sex', 'participantsBySex');
    Route::get('/participants-by-group', 'participantsByGroup');
    Route::get('/attendances-by-dependency', 'attendancesByDependency');
    Route::get('/participants-by-dependency', 'participantsByDependency');
    Route::get('/attendances-by-organization', 'attendancesByOrganization');
    Route::get('/participants-by-organization', 'participantsByOrganization');
});

// NOTA: el bloque "RUTAS DE PRUEBA" (Event::all, Participant::all, User::all, …) se retiró
// (ADR-0014). Eran endpoints públicos sin auth que filtraban toda la base y nadie consumía.
// Si se necesita un endpoint de datos, definirlo bajo ['web','auth'] (+ CampusScopeService).
