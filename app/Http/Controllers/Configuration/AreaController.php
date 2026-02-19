<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Dependency;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with('dependency')
            ->withCount('events')
            ->orderBy('name')
            ->get();

        $dependencies = Dependency::orderBy('name')->get();

        return view('administration.areas.index', compact('areas', 'dependencies'));
    }

    public function create()
    {
        // No se usa — el modal está en index
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:areas,name',
            'dependency_id' => 'required|exists:dependencies,id',
        ], [
            'name.required'          => 'El nombre del área es obligatorio.',
            'name.unique'            => 'Ya existe un área con ese nombre.',
            'dependency_id.required' => 'Debes seleccionar una dependencia.',
            'dependency_id.exists'   => 'La dependencia seleccionada no existe.',
        ]);

        Area::create($request->only('name', 'dependency_id'));

        return redirect()->route('areas.index')
            ->with('success', 'Área creada correctamente.');
    }

    public function show(Area $area)
    {
        // No se usa
    }

    public function edit(Area $area)
    {
        // No se usa — el modal está en index
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:areas,name,' . $area->id,
            'dependency_id' => 'required|exists:dependencies,id',
        ], [
            'name.required'          => 'El nombre del área es obligatorio.',
            'name.unique'            => 'Ya existe un área con ese nombre.',
            'dependency_id.required' => 'Debes seleccionar una dependencia.',
            'dependency_id.exists'   => 'La dependencia seleccionada no existe.',
        ]);

        $area->update($request->only('name', 'dependency_id'));

        return redirect()->route('areas.index')
            ->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Área eliminada correctamente.');
    }
}