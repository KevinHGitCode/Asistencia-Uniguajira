<?php

namespace App\Livewire\Event;

use App\Models\Address;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
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

    // ── Navegación por pestañas ───────────────────────────────────────────────
    // 'asistencia' | 'participante'

    public string $activeTab = 'asistencia';

    // ── Estado del flujo de asistencia ────────────────────────────────────────
    // 'search' | 'register_external' | 'found' | 'details' | 'duplicate' | 'success'

    public string $step = 'search';

    // ── Input de búsqueda (documento o código estudiantil) ────────────────────

    public string $identification = '';

    // ── Datos del participante encontrado ─────────────────────────────────────

    #[Locked]
    public ?array $participantData = null;

    public ?string $duplicateRegisteredAt = null;
    public ?string $successRegisteredAt   = null;
    public int     $totalAttendances      = 0;

    // ── Campos del detalle de asistencia (step 'details') ────────────────────

    public string $detailSexo            = '';
    public string $detailTelefono        = '';
    public string $detailMunicipio       = '';
    public string $detailBarrio          = '';
    public string $detailDireccion       = '';
    public string $detailGrupoPriorizado = '';

    // ── Campos del registro rápido como Comunidad Externa ────────────────────

    public string $externalFirstName = '';
    public string $externalLastName  = '';
    public string $externalEmail     = '';

    // ── Formulario de nuevo participante (tab) ────────────────────────────────

    public string $newDocument        = '';
    public string $newStudentCode     = '';
    public string $newFirstName       = '';
    public string $newLastName        = '';
    public string $newEmail           = '';
    public string $newRole            = 'Estudiante';
    public string $newAffiliation     = '';
    public ?int   $newProgramId       = null;
    public string $newSexo            = '';
    public string $newGrupoPriorizado = '';

    // ── Datos de apoyo ────────────────────────────────────────────────────────

    public array $programs = [];

    // ── Opciones de selección ─────────────────────────────────────────────────

    public const ROLES = [
        'Estudiante',
        'Docente',
        'Administrativo',
        'Graduado',
        'Comunidad Externa',
    ];

    public const SEXO_OPCIONES = [
        'Masculino',
        'Femenino',
        'No binario',
        'Prefiero no decir',
    ];

    public const GRUPOS_PRIORIZADOS = [
        'Víctimas del conflicto armado',
        'Población con discapacidad',
        'Comunidades indígenas',
        'Comunidades afrodescendientes',
        'Jóvenes rurales',
        'Adulto mayor',
        'LGBTIQ+',
        'Ninguno',
    ];

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

        $this->programs = Program::orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name,
                'type' => $p->program_type,
            ])
            ->toArray();
    }

    // ── Navegación por pestañas ───────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        if ($tab === $this->activeTab) {
            return;
        }

        $this->activeTab = $tab;

        if ($tab === 'asistencia') {
            $this->backToSearch();
        }
    }

    // ── Flujo de asistencia ───────────────────────────────────────────────────

    /**
     * Paso 1: buscar al participante por número de documento.
     * Transitions: search → found | register_external | duplicate
     */
    public function search(): void
    {
        $this->validate(
            ['identification' => 'required|string|max:20'],
            [
                'identification.required' => 'Ingresa tu documento o código estudiantil.',
                'identification.max'      => 'El código no puede superar los 20 caracteres.',
            ],
        );

        $term = trim($this->identification);

        $participant = Participant::with('program')
            ->where(function ($q) use ($term) {
                $q->where('document', $term)
                  ->orWhere('student_code', $term);
            })
            ->first();

        if (! $participant) {
            // No encontrado → registrar como Comunidad Externa
            $this->step = 'register_external';
            return;
        }

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
     * Registra rápidamente a alguien como Comunidad Externa y continúa al flujo normal.
     * Transitions: register_external → details
     */
    public function registerExternal(): void
    {
        $this->validate(
            [
                'externalFirstName' => 'required|string|max:100',
                'externalLastName'  => 'required|string|max:100',
                'externalEmail'     => 'nullable|email|max:255|unique:participants,email',
            ],
            [
                'externalFirstName.required' => 'El nombre es obligatorio.',
                'externalLastName.required'  => 'El apellido es obligatorio.',
                'externalEmail.email'        => 'Ingresa un correo electrónico válido.',
                'externalEmail.unique'       => 'Este correo ya está registrado en el sistema.',
            ]
        );

        try {
            $participant = Participant::create([
                'document'   => trim($this->identification),
                'first_name' => trim($this->externalFirstName),
                'last_name'  => trim($this->externalLastName),
                'email'      => $this->externalEmail ?: null,
                'role'       => 'Comunidad Externa',
            ]);

            $this->participantData = [
                'id'          => $participant->id,
                'first_name'  => $participant->first_name,
                'last_name'   => $participant->last_name,
                'document'    => $participant->document,
                'email'       => $participant->email,
                'role'        => $participant->role,
                'affiliation' => null,
                'program'     => null,
            ];

            $this->externalFirstName = '';
            $this->externalLastName  = '';
            $this->externalEmail     = '';

            $this->loadLastDefaults();
            $this->step = 'details';

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::registerExternal – ' . $e->getMessage());
            $this->addError('externalFirstName', 'Ocurrió un error al registrar. Intenta de nuevo.');
        }
    }

    /**
     * Paso 2: ir al formulario de detalles (desde 'found').
     * Pre-carga los últimos valores registrados en el sistema.
     */
    public function goToDetails(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $this->loadLastDefaults();
        $this->step = 'details';
    }

    /**
     * Paso 3: confirmar y registrar la asistencia con detalles adicionales.
     * Transitions: details → success | duplicate (race condition)
     */
    public function confirmWithDetails(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $this->validate(
            [
                'detailSexo'            => 'nullable|string|max:50',
                'detailTelefono'        => 'nullable|string|max:20',
                'detailMunicipio'       => 'nullable|string|max:100',
                'detailBarrio'          => 'nullable|string|max:100',
                'detailDireccion'       => 'nullable|string|max:255',
                'detailGrupoPriorizado' => 'nullable|string|max:150',
            ],
        );

        // Protección contra race condition
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

            // Crear dirección si se ingresó algún campo de ubicación
            $address = null;
            if ($this->detailMunicipio || $this->detailBarrio || $this->detailDireccion) {
                $address = Address::create([
                    'municipio' => $this->detailMunicipio ?: 'Sin especificar',
                    'barrio'    => $this->detailBarrio    ?: null,
                    'direccion' => $this->detailDireccion ?: null,
                ]);
            }

            // Guardar detalle de asistencia
            AttendanceDetail::create([
                'attendance_id'    => $attendance->id,
                'sexo'             => $this->detailSexo            ?: null,
                'telefono'         => $this->detailTelefono        ?: null,
                'address_id'       => $address?->id,
                'grupo_priorizado' => $this->detailGrupoPriorizado ?: null,
            ]);

            $this->successRegisteredAt = $attendance->created_at->format('h:i A');
            $this->totalAttendances    = Attendance::where('participant_id', $this->participantData['id'])->count();
            $this->step                = 'success';

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::confirmWithDetails – ' . $e->getMessage());
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

        // Limpiar campos de detalle
        $this->detailSexo            = '';
        $this->detailTelefono        = '';
        $this->detailMunicipio       = '';
        $this->detailBarrio          = '';
        $this->detailDireccion       = '';
        $this->detailGrupoPriorizado = '';

        // Limpiar campos externos
        $this->externalFirstName = '';
        $this->externalLastName  = '';
        $this->externalEmail     = '';

        $this->resetValidation();
    }

    // ── Registro de nuevo participante (tab) ──────────────────────────────────

    public function registerParticipant(): void
    {
        $this->validate(
            [
                'newDocument'        => 'required|string|max:20|unique:participants,document',
                'newStudentCode'     => 'nullable|string|max:20|unique:participants,student_code',
                'newFirstName'       => 'required|string|max:100',
                'newLastName'        => 'required|string|max:100',
                'newEmail'           => 'nullable|email|max:255|unique:participants,email',
                'newRole'            => 'required|in:Estudiante,Docente,Administrativo,Graduado,Comunidad Externa',
                'newAffiliation'     => 'nullable|in:Catedratico,Ocasional,Planta',
                'newProgramId'       => 'nullable|exists:programs,id',
                'newSexo'            => 'nullable|string|max:50',
                'newGrupoPriorizado' => 'nullable|string|max:150',
            ],
            [
                'newDocument.required'    => 'El número de documento es obligatorio.',
                'newDocument.max'         => 'El documento no puede superar 20 caracteres.',
                'newDocument.unique'      => 'Este documento ya está registrado en el sistema.',
                'newStudentCode.unique'   => 'Este código estudiantil ya está registrado.',
                'newStudentCode.max'      => 'El código no puede superar 20 caracteres.',
                'newFirstName.required'   => 'El nombre es obligatorio.',
                'newLastName.required'    => 'El apellido es obligatorio.',
                'newEmail.email'          => 'Ingresa un correo electrónico válido.',
                'newEmail.unique'         => 'Este correo ya está registrado en el sistema.',
                'newRole.required'        => 'Selecciona un rol.',
                'newRole.in'              => 'El rol seleccionado no es válido.',
                'newAffiliation.in'       => 'La afiliación seleccionada no es válida.',
            ]
        );

        try {
            $participant = Participant::create([
                'document'         => trim($this->newDocument),
                'student_code'     => $this->newStudentCode    ?: null,
                'first_name'       => trim($this->newFirstName),
                'last_name'        => trim($this->newLastName),
                'email'            => $this->newEmail           ?: null,
                'role'             => $this->newRole,
                'affiliation'      => $this->newAffiliation     ?: null,
                'program_id'       => $this->newProgramId,
                'sexo'             => $this->newSexo            ?: null,
                'grupo_priorizado' => $this->newGrupoPriorizado ?: null,
            ]);

            // Pre-cargar documento, cambiar a tab de asistencia y buscar directamente
            $this->identification = $participant->document;
            $this->resetNewParticipantForm();
            $this->activeTab = 'asistencia';
            $this->search();

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::registerParticipant – ' . $e->getMessage());
            $this->addError('newDocument', 'Ocurrió un error al registrar el participante. Intenta de nuevo.');
        }
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /** Carga los últimos valores registrados en attendance_details como valores por defecto. */
    private function loadLastDefaults(): void
    {
        $lastDetail = AttendanceDetail::with('address')->latest()->first();

        if (! $lastDetail) {
            return;
        }

        $this->detailSexo            = $lastDetail->sexo             ?? '';
        $this->detailTelefono        = $lastDetail->telefono         ?? '';
        $this->detailGrupoPriorizado = $lastDetail->grupo_priorizado ?? '';

        if ($lastDetail->address) {
            $this->detailMunicipio = $lastDetail->address->municipio ?? '';
            $this->detailBarrio    = $lastDetail->address->barrio    ?? '';
            $this->detailDireccion = $lastDetail->address->direccion ?? '';
        }
    }

    private function resetNewParticipantForm(): void
    {
        $this->newDocument        = '';
        $this->newStudentCode     = '';
        $this->newFirstName       = '';
        $this->newLastName        = '';
        $this->newEmail           = '';
        $this->newRole            = 'Estudiante';
        $this->newAffiliation     = '';
        $this->newProgramId       = null;
        $this->newSexo            = '';
        $this->newGrupoPriorizado = '';
        $this->resetValidation();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.event.attendance-registration');
    }
}
