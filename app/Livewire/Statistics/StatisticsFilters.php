<?php

namespace App\Livewire\Statistics;

use Livewire\Component;
use App\Models\Dependency;
use App\Models\User;

class StatisticsFilters extends Component
{
    public $dateFrom = '';
    public $dateTo = '';
    public $dependencyIds = [];
    public $userIds = [];
    
    public $dependencies = [];
    public $users = [];

    public function mount()
    {
        // Cargar opciones de filtros
        $this->dependencies = Dependency::orderBy('name')->get();
        $this->users = User::orderBy('name')->get();
        
        // Establecer valores por defecto (último mes)
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
    }

    public function applyFilters()
    {
        // Emitir evento con los filtros actuales
        $this->dispatch('filters-changed', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'dependencyIds' => $this->dependencyIds,
            'userIds' => $this->userIds,
        ]);
    }

    public function clearFilters()
    {
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->dependencyIds = [];
        $this->userIds = [];
        
        $this->dispatch('filters-changed', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'dependencyIds' => $this->dependencyIds,
            'userIds' => $this->userIds,
        ]);
    }

    public function toggleDependency($dependencyId)
    {
        if (in_array($dependencyId, $this->dependencyIds)) {
            $this->dependencyIds = array_values(array_diff($this->dependencyIds, [$dependencyId]));
        } else {
            $this->dependencyIds[] = $dependencyId;
        }
    }

    public function toggleUser($userId)
    {
        if (in_array($userId, $this->userIds)) {
            $this->userIds = array_values(array_diff($this->userIds, [$userId]));
        } else {
            $this->userIds[] = $userId;
        }
    }

    public function render()
    {
        return view('livewire.statistics.statistics-filters');
    }
}

