<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Schema;
use Livewire\Component;

/**
 * Campana de notificaciones in-app (ADR-0018).
 *
 * Lee las notificaciones de base de datos del usuario (canal `database` de
 * Laravel) y se refresca por poll corto, sin websockets (igual que OnlineCount,
 * pensado para hosting compartido). Al hacer clic en una notificación se marca
 * como leída y se navega a su URL.
 */
class NotificationBell extends Component
{
    public int $unreadCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    /**
     * La lista solo se carga cuando el usuario abre el desplegable, para que el
     * coste por página sea solo un COUNT indexado (no fetch + render de 10 filas
     * en cada carga de cada página).
     */
    public bool $loaded = false;

    public function mount(): void
    {
        $this->unreadCount = $this->countUnread();
    }

    public function refresh(): void
    {
        $this->unreadCount = $this->countUnread();

        // Si el desplegable ya se abrió alguna vez, mantén la lista al día.
        if ($this->loaded) {
            $this->loadItems();
        }
    }

    public function loadItems(): void
    {
        $this->loaded = true;
        $this->load();
    }

    private function countUnread(): int
    {
        if (! $this->notificationsTableExists()) {
            return 0;
        }

        return (int) (auth()->user()?->unreadNotifications()->count() ?? 0);
    }

    public function markAsRead(string $id)
    {
        if (! $this->notificationsTableExists()) {
            return null;
        }

        $user = auth()->user();
        $notification = $user?->notifications()->whereKey($id)->first();

        $url = $notification?->data['url'] ?? null;
        $notification?->markAsRead();

        $this->load();

        if ($url) {
            return $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        if (! $this->notificationsTableExists()) {
            $this->unreadCount = 0;
            $this->items = [];

            return;
        }

        auth()->user()?->unreadNotifications->markAsRead();
        $this->load();
    }

    private function load(): void
    {
        $user = auth()->user();

        if (! $user || ! $this->notificationsTableExists()) {
            $this->unreadCount = 0;
            $this->items = [];

            return;
        }

        $this->unreadCount = $user->unreadNotifications()->count();

        $this->items = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'titulo' => $n->data['titulo'] ?? 'Notificación',
                'mensaje' => $n->data['mensaje'] ?? '',
                'url' => $n->data['url'] ?? null,
                'icono' => $n->data['icono'] ?? 'bell',
                'read' => $n->read_at !== null,
                'fecha' => $n->created_at->diffForHumans(),
            ])
            ->all();
    }

    private function notificationsTableExists(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (\Throwable) {
            return false;
        }
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
