<?php

namespace App\Livewire\Event;

use App\Mail\EventModifiedMail;
use App\Models\Area;
use App\Models\Dependency;
use App\Models\Event;
use App\Services\ActivityLogService;
use App\Services\CampusScopeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class EditEventModal extends Component
{
    public $dependencies;

    public bool $isAdmin;

    public bool $showDependencySelect;

    public ?int $eventId = null;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public ?string $dependency_id = null;

    public ?string $area_id = null;

    public string $date = '';

    public string $start_time = '';

    public string $end_time = '';

    public $areas = [];

    public function mount()
    {
        $user = Auth::user();
        $campusScope = app(CampusScopeService::class);
        $this->isAdmin = $user->hasAdminAccess();

        if ($this->isAdmin) {
            $this->dependencies = $campusScope->applyToQuery(Dependency::query(), $user)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
            $this->showDependencySelect = true;
        } else {
            $userDeps = $user->dependencies()
                ->where('dependencies.campus_id', $user->campus_id)
                ->orderBy('name')
                ->pluck('dependencies.name', 'dependencies.id')
                ->toArray();

            if (count($userDeps) > 1) {
                $this->dependencies = $userDeps;
                $this->showDependencySelect = true;
            } else {
                $this->dependencies = $userDeps;
                $this->showDependencySelect = false;
            }
        }
    }

    #[On('edit-event')]
    public function loadEvent(int $id)
    {
        $event = Event::findOrFail($id);

        if (! $this->canManageEvent($event, app(CampusScopeService::class))) {
            session()->flash('error', 'No tienes permiso para editar este evento.');

            return;
        }

        $this->eventId = $event->id;
        $this->title = $event->title ?? '';
        $this->description = $event->description ?? '';
        $this->location = $event->location ?? '';
        $this->dependency_id = $event->dependency_id ? (string) $event->dependency_id : null;
        $this->area_id = $event->area_id ? (string) $event->area_id : null;
        $this->date = $event->date ?? '';
        $this->start_time = $event->start_time ? substr($event->start_time, 0, 5) : '';
        $this->end_time = $event->end_time ? substr($event->end_time, 0, 5) : '';

        $this->loadAreas();
        $this->resetValidation();
    }

    public function updatedDependencyId()
    {
        $this->area_id = null;
        $this->loadAreas();
    }

    private function loadAreas()
    {
        if (! $this->dependency_id) {
            $this->areas = [];

            return;
        }

        $query = Area::where('dependency_id', $this->dependency_id);
        $campusId = app(CampusScopeService::class)->activeCampusId(Auth::user());

        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }

        $this->areas = $query->orderBy('name')->get();
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'dependency_id' => 'nullable|exists:dependencies,id',
            'area_id' => 'nullable|exists:areas,id',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ];
    }

    public function save()
    {
        $this->validate();

        $event = Event::findOrFail($this->eventId);
        $user = Auth::user();
        $campusScope = app(CampusScopeService::class);

        if (! $this->canManageEvent($event, $campusScope)) {
            return;
        }

        $campusId = $campusScope->activeCampusId($user);
        if ($campusId === null && $user->isSuperadmin() && $event->campus_id !== null) {
            $campusId = (int) $event->campus_id;
        }

        if ($this->dependency_id) {
            $dependency = Dependency::find($this->dependency_id);

            if (! $dependency) {
                $this->addError('dependency_id', 'Dependencia no válida.');

                return;
            }

            if ($campusId !== null && (int) $dependency->campus_id !== (int) $campusId) {
                $this->addError('dependency_id', 'La dependencia no pertenece a la sede permitida.');

                return;
            }

            if (! $user->hasAdminAccess()) {
                $allowed = $user->dependencies->pluck('id')->map(fn ($id) => (int) $id)->all();

                if (! in_array((int) $this->dependency_id, $allowed, true)) {
                    $this->addError('dependency_id', 'Dependencia no válida.');

                    return;
                }
            }
        }

        $areaId = null;
        if ($this->area_id) {
            $area = Area::where('id', $this->area_id)
                ->where('dependency_id', $this->dependency_id)
                ->when($campusId !== null, fn ($query) => $query->where('campus_id', $campusId))
                ->first();

            if (! $area) {
                $this->addError('area_id', 'Área no válida para la dependencia seleccionada.');

                return;
            }
            $areaId = $area->id;
        }

        $original = $event->only([
            'title',
            'description',
            'date',
            'start_time',
            'end_time',
            'location',
            'dependency_id',
            'area_id',
            'campus_id',
        ]);

        $event->update([
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'dependency_id' => $this->dependency_id ?: null,
            'area_id' => $areaId,
            'date' => $this->date,
            'start_time' => $this->start_time ?: null,
            'end_time' => $this->end_time ?: null,
        ]);

        if (
            $event->isManuallyEnded()
            && $this->end_time
            && $this->date
        ) {
            $newEndDateTime = \Carbon\Carbon::parse($this->date.' '.$this->end_time);
            if (now()->lt($newEndDateTime)) {
                $event->update(['ended_at' => null]);
            }
        }

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $event->$field;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }

        if (! empty($changes) && $event->user?->email) {
            try {
                $event->load(['dependency', 'area', 'user']);
                Mail::to($event->user->email)->send(new EventModifiedMail($event, $changes));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de evento modificado: '.$e->getMessage());
            }
        }

        ActivityLogService::log('editar', 'eventos', "Editó el evento '{$event->title}'", $event, $changes);

        Flux::modal('edit-event-modal')->close();

        return redirect()->route('events.list')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.event.edit-event-modal');
    }

    private function canManageEvent(Event $event, CampusScopeService $campusScope): bool
    {
        $user = Auth::user();

        if (! $campusScope->canAccessResource($user, $event)) {
            return false;
        }

        if ($user->hasAdminAccess()) {
            return true;
        }

        return (int) $event->user_id === (int) $user->id;
    }
}
