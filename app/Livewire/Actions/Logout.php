<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\ActivityLogService;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        ActivityLogService::log('logout', 'sesion', 'Cerró sesión');

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
