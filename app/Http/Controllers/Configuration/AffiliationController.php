<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use Illuminate\Http\Request;

class AffiliationController extends Controller
{
    public function index()
    {
        $affiliations = Affiliation::withCount('participants')
            ->orderBy('name')
            ->get();

        return view('administration.affiliations.index', compact('affiliations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:affiliations,name',
        ], [
            'name.required' => 'El nombre de la afiliación es obligatorio.',
            'name.unique'   => 'Ya existe una afiliación con ese nombre.',
        ]);

        Affiliation::create(['name' => trim($request->name)]);

        return redirect()->route('affiliations.index')
            ->with('success', 'Afiliación creada exitosamente.');
    }
}
