<?php

namespace App\Livewire\Event;

use Livewire\Component;

class Card extends Component
{
    public $title;
    public $date;
    public $location;
    public $description;
    public $start_time;
    public $end_time;

    public function mount($title, $date, $location, $description = null, $start_time = null, $end_time = null)
    {
        $this->title = $title;
        $this->date = $date;
        $this->location = $location;
        $this->description = $description;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    public function render()
    {
        return view('livewire.event.card');
    }
}
