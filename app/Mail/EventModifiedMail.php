<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventModifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public array $changes = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Evento modificado: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-modified',
            with: [
                'event'    => $this->event,
                'user'     => $this->event->user,
                'changes'  => $this->changes,
                'showLink' => route('events.show', $this->event->id),
            ],
        );
    }
}