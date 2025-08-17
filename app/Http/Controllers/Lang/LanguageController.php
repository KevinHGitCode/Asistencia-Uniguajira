<?php

namespace App\Http\Controllers\Lang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,es'
        ]);

        Session::put('locale', $request->locale);

        // debug
        // logger('Idioma cambiado a: ' . Session::get('locale'));

        return back(); // redirige a la misma página
    }
}
