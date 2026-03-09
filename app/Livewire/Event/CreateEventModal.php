<?php

namespace App\Livewire\Event;

use App\Models\Area;
use App\Models\Event;
use App\Models\Dependency;
use App\Services\EventService;
use Livewire\Component;
use Flux;
use Flux\Flux as FluxAlias;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CreateEventModal extends Component
{
    public $dependencies;
    public $selectedDependency;
    public bool $isAdmin;
    public bool $showDependencySelect;

    // Campos del formulario
    public string $title = '';
    public string $description = '';
    public string $location = '';
    public ?string $dependency_id = null;
    public ?string $area_id = null;
    public string $date = '';
    public string $start_time = '';
    public string $end_time = '';

    // Áreas dinámicas
    public $areas = [];

    public function mount()
    {
        $user = Auth::user();
        $this->isAdmin = $user->role === 'admin';

        if ($this->isAdmin) {
            // CASO 1: Admin → todas las dependencias
            $this->dependencies = \App\Models\Dependency::pluck('name', 'id')->toArray();
            $this->showDependencySelect = true;
        } else {
            // Dependencias del usuario
            $userDeps = $user->dependencies->pluck('name', 'id')->toArray();

            if (count($userDeps) > 1) {
                // CASO 2: User con varias dependencias → select solo con las suyas
                $this->dependencies = $userDeps;
                $this->showDependencySelect = true;
            } else {
                // CASO 3: User con una sola dependencia → oculto
                $this->dependencies = $userDeps;
                $this->showDependencySelect = false;
                $this->dependency_id = (string) array_key_first($userDeps);
                $this->loadAreas();
            }
        }
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



    #[On('set-event-date')]
    public function setEventDate(string $date)
    {
        $this->date = $date;
    }

    protected function rules()
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location'      => 'nullable|string|max:255',
            'dependency_id' => 'nullable|exists:dependencies,id',
            'area_id'       => 'nullable|exists:areas,id',
            'date'          => 'required|date|after_or_equal:today',
            'start_time'    => 'nullable',
            'end_time'      => 'nullable',
        ];
    }

    public function save(EventService $eventService)
    {
        $this->validate();

        try {
            $eventService->create([
                'title'         => $this->title,
                'description'   => $this->description,
                'location'      => $this->location,
                'dependency_id' => $this->dependency_id,
                'area_id'       => $this->area_id,
                'date'          => $this->date,
                'start_time'    => $this->start_time,
                'end_time'      => $this->end_time,
            ], Auth::user());

            $this->reset(['title', 'description', 'location', 'dependency_id', 'area_id', 'date', 'start_time', 'end_time']);
            $this->areas = [];

            FluxAlias::modal('create-event-modal')->close();

            return redirect()->route('dashboard')->with('success', 'Evento creado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.event.create-event-modal');
    }
}