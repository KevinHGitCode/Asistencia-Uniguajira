<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Campus;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CampusTable extends Component
{
    use ClampsPagination, WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $campuses = $this->paginateAndClamp(Campus::query()
            ->withCount(['users', 'events', 'dependencies', 'programs', 'areas'])
            ->when(trim($this->search) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'))
            ->orderBy('name'));

        return view('livewire.administration.campus-table', compact('campuses'));
    }
}
