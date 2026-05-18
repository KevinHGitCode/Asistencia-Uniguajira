<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OrganizationTable extends Component
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
        $organizations = $this->paginateAndClamp($this->organizationQuery());

        return view('livewire.administration.organization-table', compact('organizations'));
    }

    private function organizationQuery(): Builder
    {
        $search = trim($this->search);

        return Organization::query()
            ->select(['id', 'name', 'created_at'])
            ->withCount('participantRoles')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
