<?php

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
        ->join('participants', 'attendances.participant_id', '=', 'participants.id')
        ->join('programs', 'participants.program_id', '=', 'programs.id')
        ->select('programs.name as program', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('programs.name')
        ->get();
});

// Ruta para obtener datos de roles específicos del evento
Route::get('/statistics/event/{event}/roles', function ($event) {
    return DB::table('attendances')
        ->join('participants', 'attendances.participant_id', '=', 'participants.id')
        ->select('participants.role as role', DB::raw('COUNT(*) as count'))
        ->where('attendances.event_id', $event)
        ->groupBy('participants.role')
        ->get();
});

// Distribución por sexo de un evento específico
Route::get('/statistics/event/{event}/sex', function ($event) {
    return DB::table('attendances')
        ->join('participants', 'attendances.participant_id', '=', 'participants.id')
        ->select(
            DB::raw("COALESCE(participants.sexo, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->where('attendances.event_id', $event)
        ->groupBy('participants.sexo')
        ->orderByDesc('count')
        ->get();
});

// Distribución por grupo priorizado de un evento específico
Route::get('/statistics/event/{event}/group', function ($event) {
    return DB::table('attendances')
        ->join('participants', 'attendances.participant_id', '=', 'participants.id')
        ->select(
            DB::raw("COALESCE(participants.grupo_priorizado, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->where('attendances.event_id', $event)
        ->groupBy('participants.grupo_priorizado')
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
    $eventIds = array_filter((array) $request->get('eventIds', []));

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

    $demo = fn (string $col) => DB::table('attendances')
        ->join('participants', 'attendances.participant_id', '=', 'participants.id')
        ->join('events', 'attendances.event_id', '=', 'events.id')
        ->select(
            'events.id as event_id',
            'events.title as event_title',
            DB::raw("COALESCE({$col}, 'Sin datos') as label"),
            DB::raw('COUNT(*) as count')
        )
        ->whereIn('attendances.event_id', $eventIds)
        ->groupBy('events.id', 'events.title', $col)
        ->orderBy('events.date')
        ->get();

    return [
        'attendances' => $attendances,
        'byRole'      => $demo('participants.role'),
        'bySex'       => $demo('participants.sexo'),
        'byGroup'     => $demo('participants.grupo_priorizado'),
    ];
});

/**
 * =============================================
 * RUTAS PARA LAS ESTADÍSTICAS GENERALES
 * =============================================
 */

Route::prefix('statistics')->controller(StatisticsController::class)->group(function () {
    // ── Endpoints de resumen (1 request por módulo) ──────────────────────────
    Route::get('/asistencias-summary',   'asistenciasSummary');
    Route::get('/participantes-summary', 'participantesSummary');

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

// Get participants by program ID
Route::get('/participants/program/{program_id}', function ($program_id) {
    return Participant::where('program_id', $program_id)->get();
});

// Get count of participants by program
Route::get('/participants/count-by-program', function () {
    return Participant::select('program_id', DB::raw('COUNT(*) as count'))
        ->groupBy('program_id')
        ->get();
});

// Get all roles
Route::get('/roles', function () {
    return Participant::select('role')->distinct()->get();
});

// Get all programs
Route::get('/programs', function () {
    return Program::all();
});

// Get all affiliations
Route::get('/affiliations', function () {
    return Participant::select('affiliation')->distinct()->get();
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
