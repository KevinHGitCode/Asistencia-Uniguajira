<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Language extends Component
{
    public string $language;

    public function mount()
    {
        // Carga idioma actual de sesión o config
        $this->language = session('locale', config('app.locale'));
    }

    public function updatedLanguage($value)
    {
        // Guarda en sesión
        Session::put('locale', $value);
        App::setLocale($value);
    }

    public function render()
    {
        return view('livewire.settings.language');
    }
}

