<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\Program;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $programs = $this->paginateAndClamp($this->programQuery());

        return view('livewire.administration.program-table', compact('programs'));
    }

    private function programQuery(): Builder
    {
        $search = trim($this->search);

        return Program::query()
            ->select(['id', 'name', 'program_type', 'created_at'])
            ->withCount('participants')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
