<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Language;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Http\Controllers\UserController;

use \App\Http\Controllers\Lang\LanguageController;
use App\Http\Controllers\EventController;

Route::middleware('auth')->get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('eventos/nuevo', [EventController::class, 'create'])->name('events.new');
    Route::post('eventos/nuevo', [EventController::class, 'store'])->name('events.new.store');
    Route::get('eventos/lista', [EventController::class, 'index'])->name('events.list');
});

Route::view('estadisticas', 'statistics.statistics')
    ->middleware(['auth', 'verified'])
    ->name('statistics');

Route::view('graficos/tipos', 'statistics.charts.types')
    ->middleware(['auth', 'verified'])
    ->name('charts.types');

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


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/language', Language::class)->name('settings.language');

    Route::post('settings/language/switch', [LanguageController::class, 'switch'])
        ->name('settings.language.switch');
});

Route::middleware('auth')->get('/api/mis-eventos-json', function () {
    $now = now();
    $year = $now->year;

    if ($now->month >= 1 && $now->month <= 6) {
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


require __DIR__ . '/auth.php';
