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
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        Affiliation::create(['name' => self::normalizeName($request->name)]);

        return redirect()->route('affiliations.index')
            ->with('success', 'Afiliación creada exitosamente.');
    }

    public function update(Request $request, Affiliation $affiliation)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:affiliations,name,' . $affiliation->id,
        ], [
            'name.required' => 'El nombre de la afiliación es obligatorio.',
            'name.unique'   => 'Ya existe una afiliación con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $affiliation->update(['name' => self::normalizeName($request->name)]);

        return redirect()->route('affiliations.index')
            ->with('success', 'Afiliación actualizada exitosamente.');
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
            ->with('success', "Afiliación \"{$name}\" eliminada exitosamente.");
    }

    /**
     * Normaliza: trim + colapsar espacios múltiples.
     *
     * "PROFESIONAL  -  APOYO..." → "PROFESIONAL - APOYO..."
     */
    private static function normalizeName(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value));
    }
}