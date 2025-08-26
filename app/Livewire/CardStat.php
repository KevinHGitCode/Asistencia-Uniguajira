<?php

namespace App\Livewire;

use Livewire\Component;

class CardStat extends Component
{
    public $title;
    public $value;

    public function mount($title, $value)
    {
        $this->title = $title;
        $this->value = $value;
    }

    public function render()
    {
        return view('livewire.card-stat');
    }
}

