<?php

namespace App\Livewire\User;

use App\Services\UserActivityService;
use Livewire\Component;

/**
 * Indicador en vivo de usuarios en línea (ADR-0010, frente 2).
 * Se refresca por poll corto; sin websockets.
 */
class OnlineCount extends Component
{
    public function render()
    {
        $count = app(UserActivityService::class)->onlineCount();

        return view('livewire.user.online-count', compact('count'));
    }
}
