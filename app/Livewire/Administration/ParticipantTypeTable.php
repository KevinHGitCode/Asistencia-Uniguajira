<?php

namespace App\Livewire\Administration;

use App\Livewire\Concerns\ClampsPagination;
use App\Models\ParticipantType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantTypeTable extends Component
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
        $participantTypes = $this->paginateAndClamp($this->participantTypeQuery());

        return view('livewire.administration.participant-type-table', compact('participantTypes'));
    }

    private function participantTypeQuery(): Builder
    {
        $search = trim($this->search);

        return ParticipantType::query()
            ->select(['id', 'name', 'created_at'])
            ->withCount('participants')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');
    }
}
