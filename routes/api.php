<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Program;
use App\Models\Affiliation;
use App\Models\Attendance;
use App\Models\User;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


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

Route::get('/events/{date}', [EventController::class, 'getByDate']);


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
