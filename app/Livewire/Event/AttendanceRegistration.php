<?php

namespace App\Livewire\Event;

use App\Models\Address;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Program;
use App\Models\Affiliation;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AttendanceRegistration extends Component
{
    #[Locked]
    public int $eventId = 0;

    public array $eventData = [];

    public string $activeTab = 'asistencia';

    // 'search' | 'register_external' | 'found' | 'select_program' | 'details' | 'duplicate' | 'success'
    public string $step = 'search';

    public string $identification = '';

    #[Locked]
    public ?array $participantData = null;

    public ?string $duplicateRegisteredAt = null;
    public ?string $successRegisteredAt   = null;
    public int     $totalAttendances      = 0;

    // Programa seleccionado para esta asistencia
    public ?int $selectedProgramId = null;

    // Campos del detalle de asistencia
    public string $detailSexo            = '';
    public string $detailTelefono        = '';
    public string $detailMunicipio       = '';
    public string $detailBarrio          = '';
    public string $detailDireccion       = '';
    public string $detailGrupoPriorizado = '';
    public string $detailEmail           = '';

    // Registro rapido Comunidad Externa
    public string $externalFirstName = '';
    public string $externalLastName  = '';
    public string $externalEmail     = '';

    // Formulario nuevo participante (tab)
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

    public array $programs    = [];
    public array $affiliations = [];

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
        'Prefiero no decir',
    ];

    public const GRUPOS_PRIORIZADOS = [
        'Indígena',
        'Afrodescendiente',
        'Discapacitado',
        'Víctima de Conflicto Armado',
        'Comunidad LGTBQ+',
        'Habitante de Frontera',
        'Ninguno',
    ];

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
            ->get(['id', 'name', 'campus'])
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'campus'    => $p->campus,
                'full_name' => $p->name . ($p->campus ? ' - ' . $p->campus : ''),
            ])
            ->toArray();

        $this->affiliations = Affiliation::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])
            ->toArray();
    }

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

    public function search(): void
    {
        $this->validate(
            ['identification' => 'required|string|max:20'],
            [
                'identification.required' => 'Ingresa tu documento o codigo estudiantil.',
                'identification.max'      => 'El codigo no puede superar los 20 caracteres.',
            ],
        );

        $term = trim($this->identification);

        $participant = Participant::with(['programs', 'affiliation'])
            ->where(function ($q) use ($term) {
                $q->where('document', $term)
                  ->orWhere('student_code', $term);
            })
            ->first();

        if (! $participant) {
            $this->step = 'register_external';
            return;
        }

        $programs = $participant->programs->map(fn ($p) => [
            'id'        => $p->id,
            'name'      => $p->name,
            'campus'    => $p->campus,
            'full_name' => $p->name . ($p->campus ? ' - ' . $p->campus : ''),
        ])->values()->toArray();

        $this->participantData = [
            'id'          => $participant->id,
            'first_name'  => $participant->first_name,
            'last_name'   => $participant->last_name,
            'document'    => $participant->document,
            'email'       => $participant->email,
            'has_email'   => ! empty($participant->email),
            'role'        => $participant->role,
            'affiliation' => $participant->affiliation?->name,
            'programs'    => $programs,
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
                'externalEmail.email'        => 'Ingresa un correo electronico valido.',
                'externalEmail.unique'       => 'Este correo ya esta registrado en el sistema.',
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
                'has_email'   => ! empty($participant->email),
                'role'        => $participant->role,
                'affiliation' => null,
                'programs'    => [],
            ];

            $this->externalFirstName = '';
            $this->externalLastName  = '';
            $this->externalEmail     = '';

            $this->loadLastDefaults();
            $this->step = 'details';

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::registerExternal - ' . $e->getMessage());
            $this->addError('externalFirstName', 'Ocurrio un error al registrar. Intenta de nuevo.');
        }
    }

    public function goToDetails(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $programs = $this->participantData['programs'] ?? [];

        if (count($programs) > 1) {
            $this->selectedProgramId = null;
            $this->step = 'select_program';
            return;
        }

        $this->selectedProgramId = ! empty($programs) ? $programs[0]['id'] : null;
        $this->loadLastDefaults();
        $this->step = 'details';
    }

    public function confirmProgramSelection(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $validIds = array_column($this->participantData['programs'] ?? [], 'id');

        $this->validate(
            ['selectedProgramId' => 'required|integer|in:' . implode(',', $validIds)],
            [
                'selectedProgramId.required' => 'Selecciona el programa con el que registras asistencia.',
                'selectedProgramId.in'       => 'El programa seleccionado no es valido.',
            ]
        );

        $this->loadLastDefaults();
        $this->step = 'details';
    }

    public function confirmWithDetails(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $emailRules = ($this->participantData['has_email'] ?? true)
            ? []
            : ['detailEmail' => 'nullable|email|max:255|unique:participants,email'];

        $this->validate(array_merge([
            'detailSexo'            => 'nullable|string|max:50',
            'detailTelefono'        => 'nullable|string|max:20',
            'detailMunicipio'       => 'nullable|string|max:100',
            'detailBarrio'          => 'nullable|string|max:100',
            'detailDireccion'       => 'nullable|string|max:255',
            'detailGrupoPriorizado' => 'nullable|string|max:150',
        ], $emailRules), [
            'detailEmail.email'  => 'Ingresa un correo electronico valido.',
            'detailEmail.unique' => 'Este correo ya esta registrado en el sistema.',
        ]);

        $existing = Attendance::where('event_id', $this->eventId)
            ->where('participant_id', $this->participantData['id'])
            ->first();

        if ($existing) {
            $this->duplicateRegisteredAt = $existing->created_at->format('h:i A');
            $this->step                  = 'duplicate';
            return;
        }

        try {
            if (! ($this->participantData['has_email'] ?? true) && ! empty($this->detailEmail)) {
                Participant::where('id', $this->participantData['id'])
                    ->update(['email' => strtolower(trim($this->detailEmail))]);
                $this->participantData['email']     = $this->detailEmail;
                $this->participantData['has_email'] = true;
            }

            $attendance = Attendance::create([
                'event_id'       => $this->eventId,
                'participant_id' => $this->participantData['id'],
            ]);

            $address = null;
            if ($this->detailMunicipio || $this->detailBarrio || $this->detailDireccion) {
                $address = Address::create([
                    'municipio' => $this->detailMunicipio ?: 'Sin especificar',
                    'barrio'    => $this->detailBarrio    ?: null,
                    'direccion' => $this->detailDireccion ?: null,
                ]);
            }

            AttendanceDetail::create([
                'attendance_id'    => $attendance->id,
                'sexo'             => $this->detailSexo            ?: null,
                'telefono'         => $this->detailTelefono        ?: null,
                'municipio'        => $this->detailMunicipio       ?: null,
                'barrio'           => $this->detailBarrio          ?: null,
                'direccion'        => $this->detailDireccion       ?: null,
                'grupo_priorizado' => $this->detailGrupoPriorizado ?: null,
                'program_id'       => $this->selectedProgramId,
            ]);

            $this->successRegisteredAt = $attendance->created_at->format('h:i A');
            $this->totalAttendances    = Attendance::where('participant_id', $this->participantData['id'])->count();
            $this->step                = 'success';

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::confirmWithDetails - ' . $e->getMessage());
            $this->addError('confirm', 'Ocurrio un error al registrar. Por favor, intenta de nuevo.');
        }
    }

    public function backToSearch(): void
    {
        $this->identification        = '';
        $this->participantData       = null;
        $this->duplicateRegisteredAt = null;
        $this->successRegisteredAt   = null;
        $this->totalAttendances      = 0;
        $this->selectedProgramId     = null;
        $this->step                  = 'search';

        $this->detailSexo            = '';
        $this->detailTelefono        = '';
        $this->detailMunicipio       = '';
        $this->detailBarrio          = '';
        $this->detailDireccion       = '';
        $this->detailGrupoPriorizado = '';
        $this->detailEmail           = '';

        $this->externalFirstName = '';
        $this->externalLastName  = '';
        $this->externalEmail     = '';

        $this->resetValidation();
    }

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
                'newAffiliation'     => 'nullable|string|max:100',
                'newProgramId'       => 'nullable|exists:programs,id',
                'newSexo'            => 'nullable|string|max:50',
                'newGrupoPriorizado' => 'nullable|string|max:150',
            ],
            [
                'newDocument.required'  => 'El numero de documento es obligatorio.',
                'newDocument.max'       => 'El documento no puede superar 20 caracteres.',
                'newDocument.unique'    => 'Este documento ya esta registrado en el sistema.',
                'newStudentCode.unique' => 'Este codigo estudiantil ya esta registrado.',
                'newStudentCode.max'    => 'El codigo no puede superar 20 caracteres.',
                'newFirstName.required' => 'El nombre es obligatorio.',
                'newLastName.required'  => 'El apellido es obligatorio.',
                'newEmail.email'        => 'Ingresa un correo electronico valido.',
                'newEmail.unique'       => 'Este correo ya esta registrado en el sistema.',
                'newRole.required'      => 'Selecciona un rol.',
                'newRole.in'            => 'El rol seleccionado no es valido.',
            ]
        );

        try {
            $affiliationId = null;
            if ($this->newRole === 'Docente' && ! empty($this->newAffiliation)) {
                $affiliation   = Affiliation::firstOrCreate(['name' => trim($this->newAffiliation)]);
                $affiliationId = $affiliation->id;
            }

            $participant = Participant::create([
                'document'         => trim($this->newDocument),
                'student_code'     => $this->newStudentCode    ?: null,
                'first_name'       => trim($this->newFirstName),
                'last_name'        => trim($this->newLastName),
                'email'            => $this->newEmail           ?: null,
                'role'             => $this->newRole,
                'affiliation_id'   => $affiliationId,
                'sexo'             => $this->newSexo            ?: null,
                'grupo_priorizado' => $this->newGrupoPriorizado ?: null,
            ]);

            if ($this->newProgramId && in_array($this->newRole, ['Estudiante', 'Graduado'])) {
                $participant->programs()->attach($this->newProgramId);
            }

            $this->identification = $participant->document;
            $this->resetNewParticipantForm();
            $this->activeTab = 'asistencia';
            $this->search();

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::registerParticipant - ' . $e->getMessage());
            $this->addError('newDocument', 'Ocurrio un error al registrar el participante. Intenta de nuevo.');
        }
    }

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

    public function render()
    {
        return view('livewire.event.attendance-registration');
    }
}
