<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\ParticipantType;
use Illuminate\Http\Request;

class ParticipantTypeController extends Controller
{
    public function index()
    {
        $participantTypes = ParticipantType::withCount('participants')
            ->orderBy('name')
            ->get();

        return view('administration.participant-types.index', compact('participantTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:participant_types,name',
        ], [
            'name.required' => 'El nombre del estamento es obligatorio.',
            'name.unique'   => 'Ya existe un estamento con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        ParticipantType::create(['name' => trim($request->name)]);

        return redirect()->route('participant-types.index')
            ->with('success', 'Estamento "' . trim($request->name) . '" creado exitosamente.');
    }

    public function destroy(ParticipantType $participantType)
    {
        $count = Participant::where('role', $participantType->name)->count();

        if ($count > 0) {
            return redirect()->route('participant-types.index')
                ->with('error', "No se puede eliminar \"{$participantType->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $participantType->name;
        $participantType->delete();

        return redirect()->route('participant-types.index')
            ->with('success', "Estamento \"{$name}\" eliminado exitosamente.");
    }
}
