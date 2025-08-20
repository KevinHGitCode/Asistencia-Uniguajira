<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Language;
use Illuminate\Support\Facades\Route;
use App\Models\Event;

use \App\Http\Controllers\Lang\LanguageController;
use App\Http\Controllers\EventController;
use App\Models\Participante;
use App\Models\User;

Route::middleware('auth')->get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('eventos/nuevo', [EventController::class, 'create'])->name('new');
    Route::get('eventos/lista', [EventController::class, 'index'])->name('list');
    Route::post('eventos/lista', [EventController::class, 'store'])->name('new');
});

Route::view('estadisticas', 'statistics.statistics')
    ->middleware(['auth', 'verified'])
    ->name('statistics');

Route::view('graficos/tipos', 'statistics.charts.types')
    ->middleware(['auth', 'verified'])
    ->name('charts.types');

Route::view('usuarios', 'users.users')
    ->middleware(['auth', 'verified'])
    ->name('users');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/language', Language::class)->name('settings.language');

    Route::post('settings/language/switch', [LanguageController::class, 'switch'])
        ->name('settings.language.switch');
});

Route::get('/api/event-calendar', function () {
    return Event::selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->pluck('count', 'date');
});



// Rutas para probar relaciones
// Route::get('test', function () {

//     \App\Models\Estamento::create(['tipo_estamento' => 'Docente']);
//     \App\Models\Estamento::create(['tipo_estamento' => 'Estudiante']);

//     \App\Models\Programa::create(['nombre' => 'Ingeniería de Sistemas']);
//     \App\Models\Programa::create(['nombre' => 'Ingeniería Industrial']);
//     \App\Models\Programa::create(['nombre' => 'Derecho']);
//     \App\Models\Programa::create(['nombre' => 'Administración de Empresas']);
//     \App\Models\Programa::create(['nombre' => 'Contaduría Pública']);
//     \App\Models\Programa::create(['nombre' => 'Trabajo Social']);
//     \App\Models\Programa::create(['nombre' => 'Medicina']);
//     \App\Models\Programa::create(['nombre' => 'Enfermería']);


//     // Crear un usuario
//     $user = User::create([
//         'name' => 'Daniel',
//         'email' => 'andres@example.com',
//         'password' => bcrypt('123456'),
//     ]);

//     // Crear un evento asociado a ese usuario
//     $evento = Event::create([
//         'title' => 'Congreso de Tecnología',
//         'description' => 'Evento sobre innovación y desarrollo',
//         'date' => '2025-09-01',
//         'start_time' => '09:00:00',
//         'end_time' => '12:00:00',
//         'user_id' => $user->id,
//     ]);

//     echo "Evento creado: {$evento->title} por {$user->name}";

//     $p1 = Participante::create([
//         'documento' => '12345',
//         'nombres' => 'Juan',
//         'apellidos' => 'Pérez',
//         'email' => 'juan@example.com',
//         'estamento_id' => 1,
//         'programa_id' => 2,
//     ]);

//     $p2 = Participante::create([
//         'documento' => '67890',
//         'nombres' => 'María',
//         'apellidos' => 'López',
//         'email' => 'maria@example.com',
//         'estamento_id' => 2,
//         'programa_id' => 3,
//     ]);

//     echo "Participantes creados: {$p1->id}, {$p2->id}";

//     // Asignar participantes al evento con datos extra en el pivote
//     $evento->participantes()->attach($p1->id);
//     $evento->participantes()->attach($p2->id);

//     echo "Participantes asignados al evento: {$evento->title}";
// });

// Route::get('ver-evento/{id}', function ($id) {
//     $evento = Event::with('participantes')->findOrFail($id);

//     echo "<h1>Evento: {$evento->title}</h1>";
//     echo "<p>Descripción: {$evento->description}</p>";
//     echo "<p>Fecha: {$evento->date}</p>";

//     echo "<h2>Participantes:</h2><ul>";
//     foreach ($evento->participantes as $p) {
//         echo "<li>{$p->nombres} {$p->apellidos} ({$p->documento}) - {$p->email}</li>";
//     }
//     echo "</ul>";
// });


require __DIR__ . '/auth.php';
