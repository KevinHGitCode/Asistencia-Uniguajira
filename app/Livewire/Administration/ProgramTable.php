<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Campus;
use App\Models\Program;
use App\Services\CampusScopeService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramTable extends Component
{
    use ClampsPagination;
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public string $campusId = '';

    public function mount(): void
    {
        if (auth()->user()?->isSuperadmin()) {
            $this->campusId = (string) (app(CampusScopeService::class)->activeCampusId(auth()->user()) ?? '');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCampusId(): void
    {
        $user = auth()->user();

        if (! $user?->isSuperadmin()) {
            $this->campusId = '';

            return;
        }

        if ($this->campusId !== '' && ! Campus::whereKey($this->campusId)->exists()) {
            $this->addError('campusId', 'La sede seleccionada no es válida.');
            $this->campusId = '';

            return;
        }

        session()->put(CampusScopeService::SESSION_KEY, $this->campusId === '' ? null : (int) $this->campusId);
        if ($this->campusId === '') {
            session()->forget(CampusScopeService::SESSION_KEY);
        }

        $this->resetPage();
        $this->dispatch('administration-campus-changed', campusId: $this->campusId);
    }

    public function render(): View
    {
        $programs = $this->paginateAndClamp($this->programQuery());
        $campuses = auth()->user()?->isSuperadmin()
            ? Campus::orderBy('name')->pluck('name', 'id')
            : collect();

        return view('livewire.administration.program-table', compact('programs', 'campuses'));
    }

    private function programQuery(): Builder
    {
        $search = trim($this->search);

        return Program::query()
            ->select(['id', 'name', 'program_type', 'campus_id', 'offer_location', 'academic_program_id', 'created_at'])
            ->with(['campus:id,name', 'academicProgram:id,name'])
            ->withCount('participants')
            ->tap(fn (Builder $query) => app(CampusScopeService::class)->applyToQuery($query, auth()->user()))
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
