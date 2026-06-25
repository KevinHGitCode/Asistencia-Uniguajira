<?php

namespace App\Livewire\Event;

use App\Models\Area;
use App\Models\Campus;
use App\Models\Dependency;
use App\Services\CampusScopeService;
use App\Services\EventService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateEventWizard extends Component
{
    public int $step = 1;

    public const TOTAL_STEPS = 3;

    // ── Paso 1: Identidad ─────────────────────────────────────────────────────
    public string $title = '';

    public string $description = '';

    // ── Paso 2: Organización ──────────────────────────────────────────────────
    public string $location = '';

    public ?string $campus_id = null;

    public ?string $dependency_id = null;

    public ?string $area_id = null;

    // ── Paso 3: Fecha & Hora ──────────────────────────────────────────────────
    public string $date = '';

    public string $start_time = '';

    public string $end_time = '';

    // ── UI helpers ────────────────────────────────────────────────────────────
    /** @var array<int|string, string>  id => name */
    public array $dependencies = [];

    /** @var array<int|string, string>  id => name */
    public array $campuses = [];

    /** @var array<int, array{id: int, name: string}> */
    public array $areas = [];

    public bool $isAdmin = false;

    public bool $isSuperadmin = false;

    public bool $showDependencySelect = false;

    // ── Reglas por paso ───────────────────────────────────────────────────────

    private function stepRules(): array
    {
        return [
            1 => [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ],
            2 => [
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
            ],
            3 => [
                'date' => ['required', 'date', 'after_or_equal:today'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        if ($value && $this->start_time && $value < $this->start_time) {
                            $fail(__('La hora de finalización debe ser posterior o igual a la hora de inicio.'));
                        }
                    },
                ],
            ],
        ];
    }

    // ── Ciclo de vida ─────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();
        $this->isAdmin = $user->hasAdminAccess();
        $this->isSuperadmin = $user->isSuperadmin();

        if ($this->isSuperadmin) {
            $this->campuses = Campus::orderBy('name')->pluck('name', 'id')->toArray();
            $this->dependencies = [];
            $this->showDependencySelect = true;
        } elseif ($this->isAdmin) {
            $this->campus_id = $user->campus_id !== null ? (string) $user->campus_id : null;
            $this->loadDependenciesForCampus($this->campus_id);
            $this->showDependencySelect = true;
        } else {
            $this->campus_id = $user->campus_id !== null ? (string) $user->campus_id : null;
            $userDeps = $user->dependencies()
                ->where('dependencies.campus_id', $user->campus_id)
                ->orderBy('name')
                ->get();

            if ($userDeps->count() > 1) {
                // Caso 2: usuario con varias dependencias
                $this->dependencies = $userDeps->pluck('name', 'id')->toArray();
                $this->showDependencySelect = true;
            } else {
                // Caso 3: una sola dependencia → campo oculto, áreas precargadas
                $this->dependencies = $userDeps->pluck('name', 'id')->toArray();
                $this->showDependencySelect = false;
                $this->dependency_id = (string) optional($userDeps->first())->id;
                $this->loadAreas();
            }
        }
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

    public function updatedStartTime(): void
    {
        if ($this->start_time && $this->end_time === '') {
            try {
                $this->end_time = Carbon::createFromFormat('H:i', $this->start_time)
                    ->addHour()
                    ->format('H:i');
            } catch (\Exception) {
                // formato inválido, no hacer nada
            }
        }
    }

    public function updatedDependencyId(): void
    {
        $this->area_id = null;
        $this->resetValidation(['dependency_id', 'area_id']);
        $this->loadAreas();
    }

    private function loadAreas(): void
    {
        if (! $this->dependency_id) {
            $this->areas = [];

            return;
        }

        $query = Area::select(['id', 'name'])
            ->where('dependency_id', $this->dependency_id);

        $campusId = $this->selectedCampusId();
        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }

        $this->areas = $query
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // ── Navegación ────────────────────────────────────────────────────────────

    public function nextStep(): void
    {
        $this->validate($this->stepRules()[$this->step]);
        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function save(EventService $eventService): mixed
    {
        $this->validate($this->stepRules()[3]);

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

        return redirect()->route('events.list')->with('success', '¡Evento creado exitosamente!');
    }

    public function render()
    {
        return view('livewire.event.create-event-wizard');
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

    private function selectedCampusId(): ?int
    {
        if ($this->isSuperadmin) {
            return $this->campus_id !== null && $this->campus_id !== '' ? (int) $this->campus_id : null;
        }

        return app(CampusScopeService::class)->activeCampusId(Auth::user());
    }
}
