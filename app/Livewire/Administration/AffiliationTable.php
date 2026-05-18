<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Affiliation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AffiliationTable extends Component
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
        $affiliations = $this->paginateAndClamp($this->affiliationQuery());

        return view('livewire.administration.affiliation-table', compact('affiliations'));
    }

    private function affiliationQuery(): Builder
    {
        $search = trim($this->search);

        return Affiliation::query()
            ->select(['id', 'name', 'created_at'])
            ->withCount('participants')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
