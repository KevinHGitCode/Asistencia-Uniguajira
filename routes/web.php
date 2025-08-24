<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Language;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use App\Http\Controllers\UserController;

use \App\Http\Controllers\Lang\LanguageController;
use App\Http\Controllers\EventController;

Route::middleware('auth')->get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('eventos/nuevo', [EventController::class, 'create'])->name('events.new');
    Route::post('eventos/nuevo', [EventController::class, 'store'])->name('events.new');
    Route::get('eventos/lista', [EventController::class, 'index'])->name('events.list');
});

Route::view('estadisticas', 'statistics.statistics')
    ->middleware(['auth', 'verified'])
    ->name('statistics');

Route::view('graficos/tipos', 'statistics.charts.types')
    ->middleware(['auth', 'verified'])
    ->name('charts.types');

Route::get('usuarios', [UserController::class, 'index'])
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



require __DIR__ . '/auth.php';
