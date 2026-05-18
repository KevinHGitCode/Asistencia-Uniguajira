<?php

namespace App\Livewire\Administration;

use App\Models\Dependency;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DependencyTable extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $dependencies = $this->dependencyQuery()->paginate(25);

        if ($dependencies->currentPage() > $dependencies->lastPage()) {
            $this->setPage($dependencies->lastPage());
            $dependencies = $this->dependencyQuery()->paginate(25);
        }

        return view('livewire.administration.dependency-table', compact('dependencies'));
    }

    private function dependencyQuery(): Builder
    {
        $search = trim($this->search);

        return Dependency::query()
            ->select(['id', 'name', 'created_at'])
            ->withCount(['areas', 'events', 'participants'])
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
