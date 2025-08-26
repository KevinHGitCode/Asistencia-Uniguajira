<?php

namespace App\Livewire\User;

use Livewire\Component;



class Card extends Component
{

    public $title;
    public $user;

    public function mount($title, $user) 
    {
        $this->title = $title;
        $this->user = $user;
    }


    public function render()
    {
        return view('livewire.user.card');
    }
    

}
