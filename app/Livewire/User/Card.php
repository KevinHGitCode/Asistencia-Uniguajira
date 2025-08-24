<?php

namespace App\Livewire\User;

use Livewire\Component;

class Card extends Component
{

    public $title;
   

    public function mount($title )
    {
        $this->title = $title;
        
    }


    public function render()
    {
        return view('livewire.user.card');
    }
    

}
