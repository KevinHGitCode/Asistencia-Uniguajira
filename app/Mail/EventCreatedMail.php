<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Evento creado: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-created',
            with: [
                'event'     => $this->event,
                'user'      => $this->event->user,
                'eventLink' => route('events.access', ['slug' => $this->event->link]),
                'showLink'  => route('events.show', $this->event->id),
            ],
        );
    }
}