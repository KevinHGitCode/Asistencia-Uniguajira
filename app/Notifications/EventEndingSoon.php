<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Avisa al responsable de un evento que está por finalizar (ADR-0018), para que
 * pueda cerrar el registro o descargar el PDF de asistencia a tiempo.
 */
class EventEndingSoon extends Notification
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
        $hora = $this->event->end_time
            ? \Illuminate\Support\Str::of($this->event->end_time)->substr(0, 5)
            : null;

        return [
            'tipo' => 'evento.por-finalizar',
            'titulo' => 'Un evento está por finalizar',
            'mensaje' => $hora
                ? "«{$this->event->title}» termina hoy a las {$hora}."
                : "«{$this->event->title}» está por finalizar.",
            'url' => route('events.show', $this->event->id),
            'icono' => 'clock',
        ];
    }
}
