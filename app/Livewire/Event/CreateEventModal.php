<?php

namespace App\Livewire\Event;

use App\Models\Area;
use App\Models\Campus;
use App\Models\Dependency;
use App\Services\CampusScopeService;
use App\Services\EventService;
use Flux\Flux as FluxAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateEventModal extends Component
{
    /** @var array<int|string, string> */
    public array $dependencies = [];

    /** @var array<int|string, string> */
    public array $campuses = [];

    public bool $isAdmin = false;

    public bool $isSuperadmin = false;

    public bool $showDependencySelect = false;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public ?string $campus_id = null;

    public ?string $dependency_id = null;

    public ?string $area_id = null;

    public string $date = '';

    public string $start_time = '';

    public string $end_time = '';

    public $areas = [];

    public function mount(): void
    {
        $this->configureScopeForUser();
    }

    public function updatedCampusId(): void
    {
        if (! $this->isSuperadmin) {
            return;
        }

        $this->dependency_id = null;
        $this->area_id = null;
        $this->areas = [];
        $this->resetValidation(['campus_id', 'dependency_id', 'area_id']);
        $this->loadDependenciesForCampus($this->campus_id);
    }

    public function updatedDependencyId(): void
    {
        $this->area_id = null;
        $this->resetValidation(['dependency_id', 'area_id']);
        $this->loadAreas();
    }

    #[On('set-event-date')]
    public function setEventDate(string $date): void
    {
        $this->date = $date;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'campus_id' => [
                $this->isSuperadmin ? 'required' : 'nullable',
                'integer',
                'exists:campuses,id',
            ],
            'dependency_id' => [
                'required',
                Rule::exists('dependencies', 'id')->where(function ($query) {
                    if ($this->isSuperadmin && $this->campus_id) {
                        $query->where('campus_id', $this->campus_id);
                    } elseif (! $this->isSuperadmin && Auth::user()?->campus_id) {
                        $query->where('campus_id', Auth::user()->campus_id);
                    }
                }),
            ],
            'area_id' => ['nullable', 'exists:areas,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function save(EventService $eventService): mixed
    {
        $this->validate();

        try {
            $eventService->create([
                'title' => $this->title,
                'description' => $this->description ?: null,
                'location' => $this->location ?: null,
                'campus_id' => $this->campus_id ?: null,
                'dependency_id' => $this->dependency_id ?: null,
                'area_id' => $this->area_id ?: null,
                'date' => $this->date,
                'start_time' => $this->start_time ?: null,
                'end_time' => $this->end_time ?: null,
            ], Auth::user());

            $this->reset(['title', 'description', 'location', 'campus_id', 'dependency_id', 'area_id', 'date', 'start_time', 'end_time']);
            $this->areas = [];
            $this->configureScopeForUser();

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

    private function configureScopeForUser(): void
    {
        $user = Auth::user();

        $this->isAdmin = $user?->hasAdminAccess() ?? false;
        $this->isSuperadmin = $user?->isSuperadmin() ?? false;

        if ($this->isSuperadmin) {
            $this->campuses = Campus::orderBy('name')->pluck('name', 'id')->toArray();
            $this->dependencies = [];
            $this->showDependencySelect = true;

            return;
        }

        $this->campus_id = $user?->campus_id !== null ? (string) $user->campus_id : null;

        if ($this->isAdmin) {
            $this->loadDependenciesForCampus($this->campus_id);
            $this->showDependencySelect = true;

            return;
        }

        $userDeps = $user->dependencies()
            ->where('dependencies.campus_id', $user->campus_id)
            ->orderBy('name')
            ->get();

        $this->dependencies = $userDeps->pluck('name', 'id')->toArray();

        if ($userDeps->count() > 1) {
            $this->showDependencySelect = true;

            return;
        }

        $this->showDependencySelect = false;
        $this->dependency_id = (string) optional($userDeps->first())->id;
        $this->loadAreas();
    }

    private function loadDependenciesForCampus(?string $campusId): void
    {
        if (! $campusId) {
            $this->dependencies = [];

            return;
        }

        $this->dependencies = Dependency::query()
            ->where('campus_id', $campusId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function loadAreas(): void
    {
        if (! $this->dependency_id) {
            $this->areas = [];

            return;
        }

        $campusId = $this->selectedCampusId();

        $this->areas = Area::where('dependency_id', $this->dependency_id)
            ->when($campusId !== null, fn ($query) => $query->where('campus_id', $campusId))
            ->get();
    }

    private function selectedCampusId(): ?int
    {
        if ($this->isSuperadmin) {
            return $this->campus_id !== null && $this->campus_id !== '' ? (int) $this->campus_id : null;
        }

        return app(CampusScopeService::class)->activeCampusId(Auth::user());
    }
}
