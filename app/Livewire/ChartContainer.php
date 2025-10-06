<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\View\View;
use Closure;

class ChartContainer extends Component
{
    public string $id;
    public string $class;

    public function mount(string $id, string $class = ''): void
    {
        $this->id = $id;
        $this->class = $class;
    }

    public function render(): View|Closure|string
    {
        return view('livewire.chart-container');
    }
}
