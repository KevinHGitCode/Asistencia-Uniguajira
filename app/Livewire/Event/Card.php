<?php

namespace App\Livewire\Event;

use Livewire\Component;

class Card extends Component
{
    public $title;
    public $date;
    public $location;

    public function mount($title, $date, $location)
    {
        $this->title = $title;
        $this->date = $date;
        $this->location = $location;
    }

    public function render()
    {
        return view('livewire.event.card');
    }
}
