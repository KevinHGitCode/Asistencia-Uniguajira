<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Language;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use \App\Http\Controllers\Lang\LanguageController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Configuration\AdministrationController;
use App\Http\Controllers\Configuration\AreaController;
use App\Http\Controllers\Configuration\DependencyController;

/**
 * ================================================================
 *  RUTAS PRINCIPALES DEL SISTEMA
 * ================================================================
 */
Route::middleware('auth')->get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/**
 * ================================================================
 *  RUTAS DE GESTIÓN DE EVENTOS
 * ================================================================
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('eventos/nuevo', [EventController::class, 'create'])->name('events.new');
    Route::post('eventos/nuevo', [EventController::class, 'store'])->name('events.new.store');
    Route::get('eventos/lista', [EventController::class, 'index'])->name('events.list');
    Route::get('eventos/{id}', [EventController::class, 'show'])->name('events.show');

    Route::get('eventos/{id}/descargar-asistencia', [EventController::class, 'descargarAsistencia'])
        ->name('events.download');
});

Route::middleware(['auth'])
    ->get('/dependencies/{dependency}/areas', [EventController::class, 'areas'])
    ->name('dependencies.areas');



/**
 * ================================================================
 *  RUTAS DE REGISTRO Y CONFIRMACIÓN DE ASISTENCIA
 * ================================================================
 */

// Ruta pública para mostrar confirmación de asistencia
Route::get('/events/acceso/{slug}/confirmacion/{attendanceId}', [AttendanceController::class, 'confirmation'])
    ->name('attendance.confirmation');

// Ruta pública para acceder al evento
Route::get('/events/acceso/{slug}', [EventController::class, 'access'])
    ->name('events.access');

// Ruta pública para registrar asistencia
Route::post('/events/acceso/{slug}', [AttendanceController::class, 'store'])
    ->name('attendance.store');


/**
 * ================================================================
 *  RUTAS DE ESTADÍSTICAS
 * ================================================================
 */
Route::view('estadisticas', 'statistics.statistics')
    ->middleware(['auth', 'verified'])
    ->name('statistics');

// Tipos de graficos Echarts
// Route::view('graficos/tipos', 'statistics.charts.types')
//     ->middleware(['auth', 'verified'])
//     ->name('charts.types');


/**
 * ================================================================
 *  RUTAS DE ADMINISTRACIÓN DE USUARIOS
 * ================================================================
 */
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('usuarios')
    ->group(function () {
        
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('user.form');
        Route::post('/', [UserController::class, 'store'])->name('users.store');

        // Rutas específicas
        Route::get('/{id}/information', [UserController::class, 'information'])->name('users.information');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/{id}/edit', [UserController::class, 'update'])->name('user.update');
        Route::post('/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');

        // Ruta genérica
        Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
    });

/**
 * ================================================================
 *  RUTAS DE ADMINISTRACIÓN DEL SISTEMA
 * ================================================================
 */
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('administracion')
    ->group(function () {
        
        Route::get('/', [AdministrationController::class, 'index'])->name('administracion.index');

        // Rutas específicas de dependencias
        Route::get('/dependencies', [DependencyController::class, 'index'])->name('dependencies.index');
        Route::get('/dependencies/create', [DependencyController::class, 'create'])->name('dependencies.create');
        Route::post('/dependencies', [DependencyController::class, 'store'])->name('dependencies.store');
        // Route::get('/dependencies/edit/{dependency}', [DependencyController::class, 'edit'])->name('dependencies.edit');
        Route::post('/dependencies/edit/{dependency}', [DependencyController::class, 'update'])->name('dependencies.update');
        Route::post('/dependencies/delete/{dependency}', [DependencyController::class, 'destroy'])->name('dependencies.delete');

        // Rutas específicas de áreas
        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
        Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
        Route::get('/areas/edit/{area}', [AreaController::class, 'edit'])->name('areas.edit');
        Route::post('/areas/edit/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::post('/areas/delete/{area}', [AreaController::class, 'destroy'])->name('areas.delete');
    });

/**
 * ================================================================
 *  RUTAS DE CONFIGURACIÓN DE LA CUENTA
 * ================================================================
 */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/language', Language::class)->name('settings.language');

    Route::post('settings/language/switch', [LanguageController::class, 'switch'])
        ->name('settings.language.switch');
});



require __DIR__ . '/auth.php';
