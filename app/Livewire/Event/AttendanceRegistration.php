<?php

namespace App\Livewire\Event;

use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\Affiliation;
use App\Models\Dependency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

use App\Mail\AttendanceRegisteredMail;
use Illuminate\Support\Facades\Mail;

class AttendanceRegistration extends Component
{
    #[Locked]
    public int $eventId = 0;

    public array $eventData = [];

    public string $activeTab = 'asistencia';

    // 'search' | 'register_external' | 'found' | 'select_type' | 'select_role' | 'details' | 'duplicate' | 'success'
    public string $step = 'search';

    public bool $acceptsDataTreatment = false;

    public string $identification = '';

    #[Locked]
    public ?array $participantData = null;

    public ?string $duplicateRegisteredAt = null;
    public ?string $successRegisteredAt   = null;
    public int     $totalAttendances      = 0;

    // Selección para esta asistencia
    public ?int $selectedTypeId = null;
    public ?int $selectedRoleId = null;

    // Campos del detalle de asistencia
    public string $detailGender        = '';
    public string $detailPhone         = '';
    public string $detailCity          = '';
    public string $detailNeighborhood  = '';
    public string $detailAddress       = '';
    public string $detailPriorityGroup = '';
    public string $detailEmail         = '';

    // Registro rapido Comunidad Externa
    public string $externalFirstName = '';
    public string $externalLastName  = '';
    public string $externalEmail     = '';

    // Formulario nuevo participante (tab)
    public string $newDocument       = '';
    public string $newStudentCode    = '';
    public string $newFirstName      = '';
    public string $newLastName       = '';
    public string $newEmail          = '';
    public string $newRole           = '';
    public string $newAffiliation    = '';
    public ?int   $newProgramId      = null;
    public ?int   $newDependencyId   = null;
    public string $newGender         = '';
    public string $newPriorityGroup  = '';

    public array $programs        = [];
    public array $dependencies    = [];
    public array $affiliations    = [];
    public array $participantTypes = [];

    public const GENDER_OPTIONS = [
        'Masculino',
        'Femenino',
        'Otro',
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
            ->get(['id', 'name'])
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'full_name' => $p->name,
            ])
            ->toArray();

        $this->dependencies = Dependency::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])
            ->toArray();

        $this->affiliations = Affiliation::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])
            ->toArray();

        $this->participantTypes = ParticipantType::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
            ->toArray();

        if (!empty($this->participantTypes)) {
            $this->newRole = $this->participantTypes[0]['name'];
        }
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
            [
                'identification' => 'required|string|max:20',
                'acceptsDataTreatment' => 'accepted',
            ],
            [
                'identification.required' => 'Ingresa tu documento o codigo estudiantil.',
                'identification.max'      => 'El codigo no puede superar los 20 caracteres.',
                'acceptsDataTreatment.accepted' => 'Debes aceptar el tratamiento de datos personales.',
            ],
        );

        $term = trim($this->identification);

        $participant = Participant::with(['activeRoles.type', 'activeRoles.program', 'activeRoles.dependency', 'activeRoles.affiliation'])
            ->where(function ($q) use ($term) {
                $q->where('document', $term)
                  ->orWhere('student_code', $term);
            })
            ->first();

        if (! $participant) {
            $this->step = 'register_external';
            return;
        }

        $roles = $participant->activeRoles->map(fn ($r) => [
            'id'              => $r->id,
            'type_id'         => $r->participant_type_id,
            'type_name'       => $r->type?->name ?? '',
            'program_id'      => $r->program_id,
            'program_name'    => $r->program?->name ?? null,
            'dependency_id'   => $r->dependency_id,
            'dependency_name' => $r->dependency?->name ?? null,
            'affiliation_name' => $r->affiliation?->name ?? null,
        ])->values()->toArray();

        // Tipos únicos derivados de los roles
        $types = collect($roles)
            ->unique('type_id')
            ->map(fn ($r) => ['id' => $r['type_id'], 'name' => $r['type_name']])
            ->values()
            ->toArray();

        $this->participantData = [
            'id'         => $participant->id,
            'first_name' => $participant->first_name,
            'last_name'  => $participant->last_name,
            'document'   => $participant->document,
            'email'      => $participant->email,
            'has_email'  => ! empty($participant->email),
            'roles'      => $roles,
            'types'      => $types,
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
            ]);

            $type = ParticipantType::where('name', 'Comunidad Externa')->first();
            $role = null;

            if ($type) {
                $role = ParticipantRole::create([
                    'participant_id'      => $participant->id,
                    'participant_type_id' => $type->id,
                    'program_id'          => null,
                    'dependency_id'       => null,
                    'affiliation_id'      => null,
                    'is_active'           => true,
                ]);
            }

            $roleData = $role ? [[
                'id'               => $role->id,
                'type_id'          => $type->id,
                'type_name'        => $type->name,
                'program_id'       => null,
                'program_name'     => null,
                'dependency_id'    => null,
                'dependency_name'  => null,
                'affiliation_name' => null,
            ]] : [];

            $typeData = $type ? [['id' => $type->id, 'name' => $type->name]] : [];

            $this->participantData = [
                'id'         => $participant->id,
                'first_name' => $participant->first_name,
                'last_name'  => $participant->last_name,
                'document'   => $participant->document,
                'email'      => $participant->email,
                'has_email'  => ! empty($participant->email),
                'roles'      => $roleData,
                'types'      => $typeData,
            ];

            $this->selectedRoleId = $role?->id;

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

        $types = $this->participantData['types'] ?? [];
        $roles = $this->participantData['roles'] ?? [];

        // Múltiples tipos → preguntar cuál
        if (count($types) > 1) {
            $this->selectedTypeId = null;
            $this->selectedRoleId = null;
            $this->step = 'select_type';
            return;
        }

        // Un solo tipo → seleccionar automáticamente
        $this->selectedTypeId = ! empty($types) ? $types[0]['id'] : null;

        // Filtrar roles de ese tipo
        $rolesForType = array_values(array_filter($roles, fn ($r) => $r['type_id'] === $this->selectedTypeId));

        // Múltiples roles para ese tipo → preguntar cuál
        if (count($rolesForType) > 1) {
            $this->selectedRoleId = null;
            $this->step = 'select_role';
            return;
        }

        // Un solo rol → seleccionar automáticamente
        $this->selectedRoleId = ! empty($rolesForType) ? $rolesForType[0]['id'] : null;

        $this->loadLastDefaults();
        $this->step = 'details';
    }

    public function confirmTypeSelection(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        $validIds = array_column($this->participantData['types'] ?? [], 'id');

        $this->validate(
            ['selectedTypeId' => 'required|integer|in:' . implode(',', $validIds)],
            [
                'selectedTypeId.required' => 'Selecciona el estamento con el que registras asistencia.',
                'selectedTypeId.in'       => 'El estamento seleccionado no es valido.',
            ]
        );

        $roles = $this->participantData['roles'] ?? [];
        $rolesForType = array_values(array_filter($roles, fn ($r) => $r['type_id'] === $this->selectedTypeId));

        // Múltiples roles para ese tipo → preguntar cuál
        if (count($rolesForType) > 1) {
            $this->selectedRoleId = null;
            $this->step = 'select_role';
            return;
        }

        // Un solo rol → seleccionar automáticamente
        $this->selectedRoleId = ! empty($rolesForType) ? $rolesForType[0]['id'] : null;

        $this->loadLastDefaults();
        $this->step = 'details';
    }

    public function confirmRoleSelection(): void
    {
        if (! $this->participantData) {
            $this->backToSearch();
            return;
        }

        // Solo roles del tipo seleccionado son válidos
        $rolesForType = array_filter(
            $this->participantData['roles'] ?? [],
            fn ($r) => $r['type_id'] === $this->selectedTypeId
        );
        $validIds = array_column($rolesForType, 'id');

        $this->validate(
            ['selectedRoleId' => 'required|integer|in:' . implode(',', $validIds)],
            [
                'selectedRoleId.required' => 'Selecciona el programa o dependencia con el que registras asistencia.',
                'selectedRoleId.in'       => 'La seleccion no es valida.',
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
            'detailGender'        => 'required|string|max:50',
            'detailPhone'         => 'nullable|string|max:20',
            'detailCity'          => 'nullable|string|max:100',
            'detailNeighborhood'  => 'nullable|string|max:100',
            'detailAddress'       => 'nullable|string|max:255',
            'detailPriorityGroup' => 'required|string|max:150',
        ], $emailRules), [
            'detailGender.required'        => 'Selecciona tu genero.',
            'detailPriorityGroup.required' => 'Selecciona un grupo priorizado.',
            'detailEmail.email'            => 'Ingresa un correo electronico valido.',
            'detailEmail.unique'           => 'Este correo ya esta registrado en el sistema.',
        ]);

        try {
            $result = DB::transaction(function () {
                // Verificación atómica dentro de la transacción
                $existing = Attendance::where('event_id', $this->eventId)
                    ->where('participant_id', $this->participantData['id'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return ['duplicate' => true, 'registeredAt' => $existing->created_at->format('h:i A')];
                }

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

                AttendanceDetail::create([
                    'attendance_id'       => $attendance->id,
                    'participant_role_id' => $this->selectedRoleId,
                    'gender'              => $this->detailGender        ?: null,
                    'phone'               => $this->detailPhone         ?: null,
                    'city'                => $this->detailCity          ?: null,
                    'neighborhood'        => $this->detailNeighborhood  ?: null,
                    'address'             => $this->detailAddress       ?: null,
                    'priority_group'      => $this->detailPriorityGroup ?: null,
                ]);

                return ['duplicate' => false, 'attendance' => $attendance];
            });

            if ($result['duplicate']) {
                $this->duplicateRegisteredAt = $result['registeredAt'];
                $this->step                  = 'duplicate';
                return;
            }

            $attendance = $result['attendance'];

            // Enviar correo fuera de la transacción para no bloquear
            $participantEmail = $this->participantData['email'] ?? $this->detailEmail ?? null;
            if ($participantEmail) {
                try {
                    $event = Event::with(['dependency', 'area'])->find($this->eventId);
                    $participant = Participant::find($this->participantData['id']);
                    Mail::to($participantEmail)->send(new AttendanceRegisteredMail($attendance, $event, $participant));
                } catch (\Exception $e) {
                    Log::warning('No se pudo enviar correo de asistencia: ' . $e->getMessage());
                }
            }

            $this->successRegisteredAt = $attendance->created_at->format('h:i A');
            $this->totalAttendances    = Attendance::where('participant_id', $this->participantData['id'])->count();
            $this->step                = 'success';

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // El constraint unique de BD atrapó un duplicado concurrente
            $existing = Attendance::where('event_id', $this->eventId)
                ->where('participant_id', $this->participantData['id'])
                ->first();
            $this->duplicateRegisteredAt = $existing?->created_at?->format('h:i A') ?? now()->format('h:i A');
            $this->step                  = 'duplicate';

        } catch (\Exception $e) {
            Log::error('AttendanceRegistration::confirmWithDetails - ' . $e->getMessage());
            $this->addError('confirm', 'Ocurrio un error al registrar. Por favor, intenta de nuevo.');
        }
    }

    public function backToSearch(): void
    {
        $this->acceptsDataTreatment = false;
        $this->identification        = '';
        $this->participantData       = null;
        $this->duplicateRegisteredAt = null;
        $this->successRegisteredAt   = null;
        $this->totalAttendances      = 0;
        $this->selectedTypeId        = null;
        $this->selectedRoleId        = null;
        $this->step                  = 'search';

        $this->detailGender        = '';
        $this->detailPhone         = '';
        $this->detailCity          = '';
        $this->detailNeighborhood  = '';
        $this->detailAddress       = '';
        $this->detailPriorityGroup = '';
        $this->detailEmail         = '';

        $this->externalFirstName = '';
        $this->externalLastName  = '';
        $this->externalEmail     = '';

        $this->resetValidation();
    }

    public function registerParticipant(): void
    {
        $validTypeNames = array_column($this->participantTypes, 'name');

        $this->validate(
            [
                'newDocument'       => 'required|string|max:20|unique:participants,document',
                'newStudentCode'    => 'nullable|string|max:20|unique:participants,student_code',
                'newFirstName'      => 'required|string|max:100',
                'newLastName'       => 'required|string|max:100',
                'newEmail'          => 'nullable|email|max:255|unique:participants,email',
                'newRole'           => ['required', 'string', \Illuminate\Validation\Rule::in($validTypeNames)],
                'newAffiliation'    => 'nullable|string|max:100',
                'newProgramId'      => 'nullable|exists:programs,id',
                'newDependencyId'   => 'nullable|exists:dependencies,id',
                'newGender'         => 'nullable|string|max:50',
                'newPriorityGroup'  => 'nullable|string|max:150',
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
                'newRole.required'      => 'Selecciona un estamento.',
                'newRole.in'            => 'El estamento seleccionado no es valido.',
            ]
        );

        try {
            $affiliationId = null;
            if (! empty($this->newAffiliation)) {
                $affiliation   = Affiliation::firstOrCreate(['name' => trim($this->newAffiliation)]);
                $affiliationId = $affiliation->id;
            }

            $participant = Participant::create([
                'document'       => trim($this->newDocument),
                'student_code'   => $this->newStudentCode   ?: null,
                'first_name'     => trim($this->newFirstName),
                'last_name'      => trim($this->newLastName),
                'email'          => $this->newEmail          ?: null,
            ]);

            $type = ParticipantType::where('name', $this->newRole)->first();

            if ($type) {
                ParticipantRole::create([
                    'participant_id'      => $participant->id,
                    'participant_type_id' => $type->id,
                    'program_id'          => $this->newProgramId ?: null,
                    'dependency_id'       => $this->newDependencyId ?: null,
                    'affiliation_id'      => $affiliationId,
                    'is_active'           => true,
                ]);
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

    /**
     * Devuelve los roles filtrados por el tipo seleccionado.
     * Usado en la vista para mostrar las opciones en select_role.
     */
    public function getRolesForSelectedTypeProperty(): array
    {
        if (! $this->participantData || ! $this->selectedTypeId) {
            return [];
        }

        return array_values(array_filter(
            $this->participantData['roles'] ?? [],
            fn ($r) => $r['type_id'] === $this->selectedTypeId
        ));
    }

    private function loadLastDefaults(): void
    {
        if (! $this->participantData) {
            return;
        }

        $lastDetail = AttendanceDetail::whereHas('attendance', function ($q) {
            $q->where('participant_id', $this->participantData['id']);
        })->latest()->first();

        if (! $lastDetail) {
            $this->detailGender        = '';
            $this->detailPhone         = '';
            $this->detailCity          = '';
            $this->detailNeighborhood  = '';
            $this->detailAddress       = '';
            $this->detailPriorityGroup = '';
            return;
        }

        $this->detailGender        = $lastDetail->gender         ?? '';
        $this->detailPhone         = $lastDetail->phone          ?? '';
        $this->detailCity          = $lastDetail->city           ?? '';
        $this->detailNeighborhood  = $lastDetail->neighborhood   ?? '';
        $this->detailAddress       = $lastDetail->address        ?? '';
        $this->detailPriorityGroup = $lastDetail->priority_group ?? '';
    }

    private function resetNewParticipantForm(): void
    {
        $this->newDocument      = '';
        $this->newStudentCode   = '';
        $this->newFirstName     = '';
        $this->newLastName      = '';
        $this->newEmail         = '';
        $this->newRole          = ! empty($this->participantTypes) ? $this->participantTypes[0]['name'] : '';
        $this->newAffiliation   = '';
        $this->newProgramId     = null;
        $this->newDependencyId  = null;
        $this->newGender        = '';
        $this->newPriorityGroup = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.event.attendance-registration');
    }
}
