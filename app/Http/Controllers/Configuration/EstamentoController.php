<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Estamento;
use App\Models\Participant;
use Illuminate\Http\Request;

class EstamentoController extends Controller
{
    public function index()
    {
        $estamentos = Estamento::withCount('participants')
            ->orderBy('name')
            ->get();

        return view('administration.estamentos.index', compact('estamentos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:estamentos,name',
        ], [
            'name.required' => 'El nombre del estamento es obligatorio.',
            'name.unique'   => 'Ya existe un estamento con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        Estamento::create(['name' => trim($request->name)]);

        return redirect()->route('estamentos.index')
            ->with('success', 'Estamento "' . trim($request->name) . '" creado exitosamente.');
    }

    public function destroy(Estamento $estamento)
    {
        $count = Participant::where('role', $estamento->name)->count();

        if ($count > 0) {
            return redirect()->route('estamentos.index')
                ->with('error', "No se puede eliminar \"{$estamento->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $estamento->name;
        $estamento->delete();

        return redirect()->route('estamentos.index')
            ->with('success', "Estamento \"{$name}\" eliminado exitosamente.");
    }
}
