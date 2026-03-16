<?php

namespace App\Livewire\Admin;

use App\Models\Participant;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantsList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $participants = Participant::with([
                'programs'     => fn ($q) => $q->wherePivot('is_active', 1),
                'types'        => fn ($q) => $q->wherePivot('is_active', 1),
                'affiliations' => fn ($q) => $q->wherePivot('is_active', 1),
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('document', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return view('livewire.admin.participants-list', compact('participants'));
    }
}