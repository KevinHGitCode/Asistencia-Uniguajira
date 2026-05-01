<?php

namespace App\Livewire\Event;

use App\Models\Area;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

use App\Mail\EventModifiedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $this->isAdmin = $user->role === 'admin';

        if ($this->isAdmin) {
            $this->dependencies = \App\Models\Dependency::pluck('name', 'id')->toArray();
            $this->showDependencySelect = true;
        } else {
            $userDeps = $user->dependencies->pluck('name', 'id')->toArray();

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

        // Verificar que el usuario puede editar este evento
        $user = Auth::user();
        if ($user->role !== 'admin' && $event->user_id !== $user->id) {
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

        // Flux::modal('edit-event-modal')->show();
    }

    public function updatedDependencyId()
    {
        $this->area_id = null;
        $this->loadAreas();
    }

    private function loadAreas()
    {
        if ($this->dependency_id) {
            $this->areas = Area::where('dependency_id', $this->dependency_id)->get();
        } else {
            $this->areas = [];
        }
    }

    protected function rules()
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location'      => 'nullable|string|max:255',
            'dependency_id' => 'nullable|exists:dependencies,id',
            'area_id'       => 'nullable|exists:areas,id',
            'date'          => 'required|date',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i',
        ];
    }

    public function save()
    {
        $this->validate();

        $event = Event::findOrFail($this->eventId);
        $user = Auth::user();

        // Seguridad: verificar permisos
        if ($user->role !== 'admin' && $event->user_id !== $user->id) {
            return;
        }

        // Seguridad dependencia (usuario normal)
        if ($user->role !== 'admin' && $this->dependency_id) {
            $allowed = $user->dependencies->pluck('id')->toArray();
            if (!in_array($this->dependency_id, $allowed)) {
                $this->addError('dependency_id', 'Dependencia no válida.');
                return;
            }
        }

        // Seguridad área
        $areaId = null;
        if ($this->area_id) {
            $area = Area::where('id', $this->area_id)
                ->where('dependency_id', $this->dependency_id)
                ->first();

            if (!$area) {
                $this->addError('area_id', 'Área no válida para la dependencia seleccionada.');
                return;
            }
            $areaId = $area->id;
        }

        $original = $event->only(['title', 'description', 'date', 'start_time', 'end_time', 'location', 'dependency_id', 'area_id']);

        $event->update([
            'title'         => $this->title,
            'description'   => $this->description,
            'location'      => $this->location,
            'dependency_id' => $this->dependency_id ?: null,
            'area_id'       => $areaId,
            'date'          => $this->date,
            'start_time'    => $this->start_time ?: null,
            'end_time'      => $this->end_time ?: null,
        ]);

        // Detectar cambios y enviar correo
        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $event->$field;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }

        if (!empty($changes) && $event->user?->email) {
            try {
                $event->load(['dependency', 'area', 'user']);
                Mail::to($event->user->email)->send(new EventModifiedMail($event, $changes));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de evento modificado: ' . $e->getMessage());
            }
        }


        Flux::modal('edit-event-modal')->close();

        return redirect()->route('events.list')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    public function render()
    {
        return view('livewire.event.edit-event-modal');
    }
}