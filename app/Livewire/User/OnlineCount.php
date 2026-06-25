<?php

namespace App\Livewire\User;

use App\Services\UserActivityService;
use Livewire\Component;

/**
 * Indicador en vivo de usuarios en línea (ADR-0010, frente 2).
 *
 * Se refresca por poll corto (sin websockets). Además del contador del header,
 * emite el evento de navegador `online-users-updated` con los IDs en línea para
 * que la tabla (Alpine) muestre/oculte el punto verde de cada avatar sin recargar.
 */
class OnlineCount extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = app(UserActivityService::class)->onlineCount();
    }

    public function refresh(): void
    {
        $ids = app(UserActivityService::class)->onlineUserIds();
        $this->count = count($ids);

        // Notifica a la tabla los IDs en línea actuales (Alpine escucha en window).
        $this->dispatch('online-users-updated', ids: array_values($ids));
    }

    public function render()
    {
        return view('livewire.user.online-count');
    }
}
