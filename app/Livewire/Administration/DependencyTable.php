<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Campus;
use App\Models\Dependency;
use App\Services\CampusScopeService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DependencyTable extends Component
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
        $dependencies = $this->paginateAndClamp($this->dependencyQuery());
        $campuses = auth()->user()?->isSuperadmin()
            ? Campus::orderBy('name')->pluck('name', 'id')
            : collect();

        return view('livewire.administration.dependency-table', compact('dependencies', 'campuses'));
    }

    private function dependencyQuery(): Builder
    {
        $search = trim($this->search);

        return Dependency::query()
            ->select(['id', 'name', 'campus_id', 'created_at'])
            ->with('campus:id,name')
            ->withCount('events')
            ->withCount([
                'participantRoles as participants_count' => fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->select(DB::raw('count(distinct participant_id)')),
            ])
            ->tap(fn (Builder $query) => app(CampusScopeService::class)->applyToQuery($query, auth()->user()))
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
