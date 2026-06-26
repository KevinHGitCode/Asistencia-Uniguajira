<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Avisa al responsable de un evento que está por comenzar (ADR-0018).
 */
class EventStartingSoon extends Notification
{
    use Queueable;

    public function __construct(public Event $event) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $hora = $this->event->start_time
            ? \Illuminate\Support\Str::of($this->event->start_time)->substr(0, 5)
            : null;

        return [
            'tipo' => 'evento.proximo',
            'titulo' => 'Tienes un evento próximo',
            'mensaje' => $hora
                ? "«{$this->event->title}» comienza hoy a las {$hora}."
                : "«{$this->event->title}» está por comenzar.",
            'url' => route('events.show', $this->event->id),
            'icono' => 'calendar-days',
        ];
    }
}
