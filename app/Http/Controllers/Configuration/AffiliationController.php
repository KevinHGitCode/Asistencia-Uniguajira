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
            'name.required' => 'El nombre de la afiliacion es obligatorio.',
            'name.unique'   => 'Ya existe una afiliacion con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        Affiliation::create(['name' => trim($request->name)]);

        return redirect()->route('affiliations.index')
            ->with('success', 'Afiliacion creada exitosamente.');
    }

    public function update(Request $request, Affiliation $affiliation)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:affiliations,name,' . $affiliation->id,
        ], [
            'name.required' => 'El nombre de la afiliacion es obligatorio.',
            'name.unique'   => 'Ya existe una afiliacion con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $affiliation->update(['name' => trim($request->name)]);

        return redirect()->route('affiliations.index')
            ->with('success', 'Afiliacion actualizada exitosamente.');
    }

    public function destroy(Affiliation $affiliation)
    {
        $affiliation->loadCount('participants');
        $count = $affiliation->participants_count;

        if ($count > 0) {
            return redirect()->route('affiliations.index')
                ->with('error', "No se puede eliminar \"{$affiliation->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $affiliation->name;
        $affiliation->delete();

        return redirect()->route('affiliations.index')
            ->with('success', "Afiliacion \"{$name}\" eliminada exitosamente.");
    }
}

