<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Dependency;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DependencyTable extends Component
{
    use ClampsPagination;
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $dependencies = $this->paginateAndClamp($this->dependencyQuery());

        return view('livewire.administration.dependency-table', compact('dependencies'));
    }

    private function dependencyQuery(): Builder
    {
        $search = trim($this->search);

        return Dependency::query()
            ->select(['id', 'name', 'created_at'])
            ->withCount(['events', 'participants'])
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
