<?php

namespace App\Livewire;

use Livewire\Component;

class CardStat extends Component
{
    public $title;
    public $value;
    public $icon;

    public function mount($title, $value, $icon = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->icon  = $icon;
    }

    public function render()
    {
        return view('livewire.card-stat');
    }
}

