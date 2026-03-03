<?php

namespace App\Livewire\User;

use Livewire\Component;



class Card extends Component
{

    public $title;
    public $user;
    public bool $showDependenciesUpward = false;

    public function mount($title, $user, bool $showDependenciesUpward = false) 
    {
        $this->title = $title;
        $this->user = $user;
        $this->showDependenciesUpward = $showDependenciesUpward;
    }


    public function render()
    {
        return view('livewire.user.card');
    }
    

}
