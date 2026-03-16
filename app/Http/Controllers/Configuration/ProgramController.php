<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('participants')
            ->orderBy('name')
            ->orderBy('campus')
            ->get();

        return view('administration.programs.index', compact('programs'));
    }

    public function store(Request $request)
    {
        $this->validateProgram($request);

        Program::create([
            'name'         => trim($request->name),
            'campus'       => $request->campus ? trim($request->campus) : null,
            'program_type' => $request->program_type ?: null,
        ]);

        return redirect()->route('programs.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function update(Request $request, Program $program)
    {
        $this->validateProgram($request, $program->id);

        $program->update([
            'name'         => trim($request->name),
            'campus'       => $request->campus ? trim($request->campus) : null,
            'program_type' => $request->program_type ?: null,
        ]);

        return redirect()->route('programs.index')
            ->with('success', 'Programa actualizado exitosamente.');
    }

    public function destroy(Program $program)
    {
        $program->loadCount('participants');
        $count = $program->participants_count;

        if ($count > 0) {
            return redirect()->route('programs.index')
                ->with('error', "No se puede eliminar \"{$program->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $program->name;
        $program->delete();

        return redirect()->route('programs.index')
            ->with('success', "Programa \"{$name}\" eliminado exitosamente.");
    }

    private function validateProgram(Request $request, ?int $ignoreId = null): void
    {
        $campus = $request->campus ? trim($request->campus) : null;

        $uniqueRule = Rule::unique('programs')
            ->where(fn ($q) => $q->where('campus', $campus));

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        $request->validate([
            'name'         => ['required', 'string', 'max:100', $uniqueRule],
            'campus'       => ['nullable', 'string', 'max:100'],
            'program_type' => ['nullable', Rule::in(['Pregrado', 'Posgrado'])],
        ], [
            'name.required'        => 'El nombre del programa es obligatorio.',
            'name.unique'          => 'Ya existe un programa con ese nombre y sede.',
            'name.max'             => 'El nombre no puede superar los 100 caracteres.',
            'campus.max'           => 'La sede no puede superar los 100 caracteres.',
            'program_type.in'      => 'El tipo de programa no es válido.',
        ]);
    }
}
