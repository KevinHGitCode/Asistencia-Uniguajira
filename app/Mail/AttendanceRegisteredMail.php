<?php

namespace App\Mail;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Attendance $attendance,
        public Event $event,
        public Participant $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Asistencia registrada: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendance-registered',
            with: [
                'attendance'  => $this->attendance,
                'event'       => $this->event,
                'participant' => $this->participant,
            ],
        );
    }
}