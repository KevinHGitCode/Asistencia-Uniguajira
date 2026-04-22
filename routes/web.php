<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Settings\About;
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
use App\Http\Controllers\Configuration\AffiliationController;
use App\Http\Controllers\Configuration\AreaController;
use App\Http\Controllers\Configuration\DependencyController;
use App\Http\Controllers\Configuration\ParticipantTypeController;
use App\Http\Controllers\Configuration\ProgramController;
use App\Http\Controllers\Configuration\FormatController;
use App\Http\Controllers\Configuration\ParticipantImportController;

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
    Route::delete('eventos/{id}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('eventos/{id}/descargar-asistencia/{formatSlug?}', [EventController::class, 'descargarAsistencia'])
        ->name('events.download');
});

Route::middleware(['auth'])
    ->get('/dependencies/{dependency}/areas', [EventController::class, 'areas'])
    ->name('dependencies.areas');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/events', function () {
        return view('events.admin-events');
    })->name('admin.events.index');
});


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
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('estadisticas',                   'statistics.statistics')       ->name('statistics');
    Route::view('estadisticas/asistencias',       'statistics.asistencias')      ->name('statistics.asistencias');
    Route::view('estadisticas/participantes',     'statistics.participantes')    ->name('statistics.participantes');
    Route::view('estadisticas/eventos',           'statistics.eventos')          ->name('statistics.eventos');
    Route::view('estadisticas/compara-eventos',   'statistics.compara-eventos')  ->name('statistics.compara-eventos');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('estadisticas/usuarios', 'statistics.usuarios')->name('statistics.usuarios');
});


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
        Route::post('/dependencies/import', [DependencyController::class, 'importExcel'])->name('dependencies.import');
        Route::get('/dependencies/download-template', [DependencyController::class, 'downloadTemplate'])->name('dependencies.download-template');
        Route::get('/dependencies/download-export', [DependencyController::class, 'downloadExport'])->name('dependencies.download-export');
        Route::post('/dependencies', [DependencyController::class, 'store'])->name('dependencies.store');
        // Route::get('/dependencies/edit/{dependency}', [DependencyController::class, 'edit'])->name('dependencies.edit');
        Route::post('/dependencies/edit/{dependency}', [DependencyController::class, 'update'])->name('dependencies.update');
        Route::post('/dependencies/delete/{dependency}', [DependencyController::class, 'destroy'])->name('dependencies.delete');

        // Rutas específicas de áreas
        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
        Route::post('/areas/import', [AreaController::class, 'importExcel'])->name('areas.import');
        Route::get('/areas/download-template', [AreaController::class, 'downloadTemplate'])->name('areas.download-template');
        Route::get('/areas/download-export', [AreaController::class, 'downloadExport'])->name('areas.download-export');
        Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
        Route::get('/areas/edit/{area}', [AreaController::class, 'edit'])->name('areas.edit');
        Route::post('/areas/edit/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::post('/areas/delete/{area}', [AreaController::class, 'destroy'])->name('areas.delete');


        // Rutas específicas de formatos
        Route::get('/formats', [FormatController::class, 'index'])->name('formats.index');
        // Route::get('/formats/create', [FormatController::class, 'create'])->name('formats.create');  // TODO: Esta ruta no se usa
        Route::post('/formats', [FormatController::class, 'store'])->name('formats.store');
        Route::post('/formats/edit/{format}', [FormatController::class, 'update'])->name('formats.update');
        Route::post('/formats/delete/{format}', [FormatController::class, 'destroy'])->name('formats.destroy');
        Route::post('/formats/{format}/dependencies', [FormatController::class, 'syncDependencies'])->name('formats.sync-dependencies');

        Route::get('/formats/{format}/mapper', [FormatController::class, 'mapper'])->name('formats.mapper');
        Route::post('/formats/{format}/mapping', [FormatController::class, 'saveMapping'])->name('formats.save-mapping');

        // Rutas de afiliaciones
        Route::get('/affiliations', [AffiliationController::class, 'index'])->name('affiliations.index');
        Route::post('/affiliations', [AffiliationController::class, 'store'])->name('affiliations.store');
        Route::post('/affiliations/edit/{affiliation}', [AffiliationController::class, 'update'])->name('affiliations.update');
        Route::delete('/affiliations/{affiliation}', [AffiliationController::class, 'destroy'])->name('affiliations.destroy');
        Route::post('/affiliations/import', [AffiliationController::class, 'importExcel'])->name('affiliations.import');
        Route::get('/affiliations/download-template', [AffiliationController::class, 'downloadTemplate'])->name('affiliations.download-template');
        Route::get('/affiliations/download-export', [AffiliationController::class, 'downloadExport'])->name('affiliations.download-export');

        // Rutas de programas
        Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
        Route::post('/programs/import', [ProgramController::class, 'importExcel'])->name('programs.import');
        Route::get('/programs/download-skipped', [ProgramController::class, 'downloadSkipped'])->name('programs.download-skipped');
        Route::get('/programs/download-template', [ProgramController::class, 'downloadTemplate'])->name('programs.download-template');
        Route::get('/programs/download-export', [ProgramController::class, 'downloadExport'])->name('programs.download-export');
        Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
        Route::post('/programs/edit/{program}', [ProgramController::class, 'update'])->name('programs.update');
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

        // Rutas de estamentos / tipos de participante
        Route::get('/participant-types', [ParticipantTypeController::class, 'index'])->name('participant-types.index');
        Route::post('/participant-types', [ParticipantTypeController::class, 'store'])->name('participant-types.store');
        Route::post('/participant-types/edit/{participantType}', [ParticipantTypeController::class, 'update'])->name('participant-types.update');
        Route::delete('/participant-types/{participantType}', [ParticipantTypeController::class, 'destroy'])->name('participant-types.destroy');
        Route::post('/participant-types/import', [ParticipantTypeController::class, 'importExcel'])->name('participant-types.import');
        Route::get('/participant-types/download-template', [ParticipantTypeController::class, 'downloadTemplate'])->name('participant-types.download-template');
        Route::get('/participant-types/download-export', [ParticipantTypeController::class, 'downloadExport'])->name('participant-types.download-export');

        // Rutas de importación / registro de participantes
        Route::get('/participants', [ParticipantImportController::class, 'index'])->name('participants-import.index');
        Route::post('/participants/import', [ParticipantImportController::class, 'import'])->name('participants-import.import');
        Route::get('/participants/download-skipped', [ParticipantImportController::class, 'downloadSkipped'])->name('participants-import.download-skipped');
        Route::get('/participants/download-template', [ParticipantImportController::class, 'downloadTemplate'])->name('participants-import.download-template');
        Route::post('/participants', [ParticipantImportController::class, 'store'])->name('participants-import.store');

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
    Route::get('settings/about', About::class)->name('settings.about');

    Route::post('settings/language/switch', [LanguageController::class, 'switch'])
        ->name('settings.language.switch');
});



require __DIR__ . '/auth.php';
