<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CardStat extends Component
{
    public $title;
    public $value;
    public $user;

    public function mount($title, $value, $user=null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->user = $user ?? Auth::user();
    }

    public function render()
    {
        return view('livewire.card-stat');
    }
}

