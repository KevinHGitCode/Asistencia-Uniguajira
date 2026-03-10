<?php

namespace App\Livewire\Event;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AttendanceRegistration extends Component
{
    // ── Identificadores (inmutables tras mount) ───────────────────────────────

    #[Locked]
    public int $eventId = 0;

    /** Datos del evento para mostrar en el header (solo lectura). */
    public array $eventData = [];

    // ── Estado del flujo ──────────────────────────────────────────────────────
    // Valores: 'search' | 'not_found' | 'found' | 'duplicate' | 'success'

    public string $step = 'search';

    // ── Input del usuario ─────────────────────────────────────────────────────

    public string $identification = '';

    // ── Datos cargados del participante ───────────────────────────────────────

    #[Locked]
    public ?array $participantData = null;

    /** Hora en que ya había registrado asistencia (solo en step 'duplicate'). */
    public ?string $duplicateRegisteredAt = null;

    /** Hora en que se acaba de registrar la asistencia (solo en step 'success'). */
    public ?string $successRegisteredAt = null;

    /** Cuántas asistencias totales acumula el participante (step 'success'). */
    public int $totalAttendances = 0;

    // ── Ciclo de vida ─────────────────────────────────────────────────────────

    public function mount(string $slug): void
    {
        $event = Event::where('link', $slug)->firstOrFail();

        $this->eventId   = $event->id;
        $this->eventData = [
            'title'      => $event->title,
            'date'       => $event->date,
            'start_time' => $event->start_time,
            'end_time'   => $event->end_time,
            'location'   => $event->location,
        ];
    }

    // ── Acciones ──────────────────────────────────────────────────────────────

    /**
     * Paso 1: buscar al participante por número de documento.
     * Transitions: search → found | not_found | duplicate
     */
    public function search(): void
    {
        $this->validate(
            ['identification' => 'required|string|max:20'],
            [
                'identification.required' => 'Ingresa tu número de documento.',
                'identification.max'      => 'El documento no puede superar los 20 caracteres.',
            ],
        );

        $participant = Participant::with('program')
            ->where('document', trim($this->identification))
            ->first();

        if (! $participant) {
            $this->step = 'not_found';
            return;
        }

        // Guardar como array para evitar serialización de modelos Eloquent
        $this->participantData = [
            'id'          => $participant->id,
            'first_name'  => $participant->first_name,
            'last_name'   => $participant->last_name,
            'document'    => $participant->document,
            'email'       => $participant->email,
            'role'        => $participant->role,
            'affiliation' => $participant->affiliation,
            'program'     => $participant->program?->name,
        ];

        // ¿Ya tiene asistencia registrada en este evento?
        $existing = Attendance::where('event_id', $this->eventId)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing) {
            $this->duplicateRegisteredAt = $existing->created_at->format('h:i A');
            $this->step                  = 'duplicate';
            return;
        }

        $this->step = 'found';
    }

    /**
     * Paso 2: confirmar y registrar la asistencia.
     * Transition: found → success | duplicate (si hubo race condition)
     */
    public function confirm(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        // Protección contra doble envío / race condition
        $existing = Attendance::where('event_id', $this->eventId)
            ->where('participant_id', $this->participantData['id'])
            ->first();

        if ($existing) {
            $this->duplicateRegisteredAt = $existing->created_at->format('h:i A');
            $this->step                  = 'duplicate';
            return;
        }

        try {
            $attendance = Attendance::create([
                'event_id'       => $this->eventId,
                'participant_id' => $this->participantData['id'],
            ]);

            $this->successRegisteredAt = $attendance->created_at->format('h:i A');
            $this->totalAttendances    = Attendance::where('participant_id', $this->participantData['id'])->count();
            $this->step                = 'success';
        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::confirm – ' . $e->getMessage());
            $this->addError('confirm', 'Ocurrió un error al registrar. Por favor, intenta de nuevo.');
        }
    }

    /** Vuelve al formulario inicial limpiando todo el estado. */
    public function backToSearch(): void
    {
        $this->identification        = '';
        $this->participantData       = null;
        $this->duplicateRegisteredAt = null;
        $this->successRegisteredAt   = null;
        $this->totalAttendances      = 0;
        $this->step                  = 'search';
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.event.attendance-registration');
    }
}
