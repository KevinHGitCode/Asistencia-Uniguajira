<?php

use App\Http\Controllers\Api\AdminEventosController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Dependency;
use App\Models\Affiliation;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



/**
 * ================================================================
 *  RUTAS PARA LA GESTION DEL CALENDARIO
 * ================================================================
 */
// Ruta para obtener eventos en formato JSON para el calendario
Route::get('/eventos-json', function () {
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
    return Event::selectRaw('DATE(date) as date, COUNT(*) as count')
        ->whereBetween('date', [$start, $end])
        ->groupBy('date')
        ->get();
});

// Ruta para obtener eventos por fecha específica
Route::get('/events/{date}', [EventController::class, 'getByDate']);

// Ruta para obtener eventos del usuario autenticado en formato JSON para el calendario
Route::middleware(['web', 'auth'])->get('/mis-eventos-json', function () {
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
        'eventos' => Event::selectRaw('DATE_FORMAT(date, "%Y-%m-%d") as date, COUNT(*) as count')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$start, $end])
            ->groupBy('date')
            ->get()
    ]);
});




/**
 * =============================================
 * RUTAS PARA LAS ESTADÍSTICAS ESPECÍFICAS DEL EVENTO
 * =============================================
 */

// Ruta para obtener datos de programas específicos del evento
Route::get('/statistics/event/{event}/programs', function ($event) {
    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('programs', 'attendance_details.program_id', '=', 'programs.id')
        ->select('programs.name as program', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('programs.name')
        ->orderByDesc('count')
        ->get();
});

// Ruta para obtener estamentos específicos del evento
Route::get('/statistics/event/{event}/roles', function ($event) {
    return DB::table('attendances')
        ->join('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id')
        ->join('participant_types', 'attendance_details.participant_type_id', '=', 'participant_types.id')
        ->select('participant_types.name as role', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('participant_types.name')
        ->orderByDesc('count')
        ->get();
});

// Distribución por género de un evento específico
Route::get('/statistics/event/{event}/sex', function ($event) {
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
Route::get('/statistics/event/{event}/group', function ($event) {
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

/**
 * =============================================
 * RUTAS PARA COMPARAR EVENTOS
 * =============================================
 */

// Lista de eventos disponibles para comparar (admin ve todos; usuario ve solo los suyos)
Route::middleware(['web', 'auth'])->get('/statistics/compare/events', function (Request $request) {
    $user    = Auth::user();
    $isAdmin = $user->role === 'admin';

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

    if (!$isAdmin) {
        $query->where('events.user_id', $user->id);
    }

    $dateFrom = $request->get('dateFrom');
    $dateTo   = $request->get('dateTo');
    if ($dateFrom) $query->where('events.date', '>=', $dateFrom);
    if ($dateTo)   $query->where('events.date', '<=', $dateTo);

    return $query->get();
});

// Datos comparativos para los eventos seleccionados (asistencias + demografía)
Route::get('/statistics/compare/data', function (Request $request) {
    $eventIds = array_values(array_filter(array_map('intval', (array) $request->get('eventIds', []))));

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
        ->join('participant_types', 'attendance_details.participant_type_id', '=', 'participant_types.id')
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
        'byRole'      => $byRole,
        'bySex'       => $demoDetail('gender'),
        'byGroup'     => $demoDetail('priority_group'),
    ];
});



// ✅ Usa sesión web + auth, igual que las demás rutas
Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/statistics/admin-eventos',                [AdminEventosController::class, 'index']);
    Route::get('/statistics/admin-eventos/filter-options', [AdminEventosController::class, 'filterOptions']);
});

/**
 * =============================================
 * RUTAS PARA LAS ESTADÍSTICAS GENERALES
 * =============================================
 */

// ── Endpoints de resumen: requieren sesión para filtrar por rol de usuario ──
Route::middleware(['web', 'auth'])->prefix('statistics')->controller(StatisticsController::class)->group(function () {
    Route::get('/asistencias-summary',   'asistenciasSummary');
    Route::get('/participantes-summary', 'participantesSummary');
});

Route::prefix('statistics')->controller(StatisticsController::class)->group(function () {
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
    Route::get('/attendances-by-role',   'attendancesByRole');
    Route::get('/attendances-by-sex',    'attendancesBySex');
    Route::get('/attendances-by-group',  'attendancesByGroup');
    Route::get('/participants-by-role',  'participantsByRole');
    Route::get('/participants-by-sex',   'participantsBySex');
    Route::get('/participants-by-group', 'participantsByGroup');
});

/**
 * =============================================
 * RUTAS DE PRUEBA
 * =============================================
 */
// Route to get all events in JSON format
// Todas estas rutas tiene el prefix /api

// Get all events
Route::get('/events', function () {
    return Event::all();
});

// Get events by user ID
Route::get('/events/user/{user_id}', function ($user_id) {
    return Event::where('user_id', $user_id)->get();
});

//consultar eventos con información del usuario
Route::get('/events-with-user', function () {
    return Event::with('user')->get();
});

// Get all participants
Route::get('/participants', function () {
    return Participant::all();
});

// Get participants by program ID (via pivot)
Route::get('/participants/program/{program_id}', function ($program_id) {
    return DB::table('participants')
        ->join('participant_program', 'participants.id', '=', 'participant_program.participant_id')
        ->where('participant_program.program_id', $program_id)
        ->select('participants.*')
        ->get();
});

// Get count of participants by program (via pivot)
Route::get('/participants/count-by-program', function () {
    return DB::table('participant_program')
        ->join('programs', 'participant_program.program_id', '=', 'programs.id')
        ->select('programs.name as program', DB::raw('COUNT(DISTINCT participant_program.participant_id) as count'))
        ->groupBy('programs.id', 'programs.name')
        ->orderByDesc('count')
        ->get();
});

// Get all estamentos (participant types)
Route::get('/roles', function () {
    return \App\Models\ParticipantType::orderBy('name')->get(['id', 'name']);
});

// Get all programs
Route::get('/programs', function () {
    return Program::all();
});

// Get all affiliations
Route::get('/affiliations', function () {
    return Affiliation::all();
});

// Get all attendances
Route::get('/attendances', function () {
    return Attendance::all();
});

// Get all users
Route::get('/users', function () {
    return User::all();
});

// Get all dependencies
Route::get('/dependencies', function () {
    return Dependency::all();
});
