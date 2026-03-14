<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Format;
use App\Models\Dependency;
use Illuminate\Http\Request;

class FormatController extends Controller
{
    public function index()
    {
        $formats = Format::with('dependencies')
            ->withCount('dependencies')
            ->get();

        $dependencies = Dependency::orderBy('name')->get();

        return view('administration.formats.index', compact('formats', 'dependencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:formats,slug',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique'   => 'Ya existe un formato con ese identificador.',
        ]);

        $format = Format::create($request->only('name', 'slug'));

        if ($request->has('dependencies')) {
            $format->dependencies()->sync($request->dependencies);
        }

        return redirect()->route('formats.index')->with('success', 'Formato creado correctamente.');
    }

    public function update(Request $request, Format $format)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:formats,slug,' . $format->id,
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique'   => 'Ya existe un formato con ese identificador.',
        ]);

        $format->update($request->only('name', 'slug'));
        $format->dependencies()->sync($request->dependencies ?? []);

        return redirect()->route('formats.index')->with('success', 'Formato actualizado correctamente.');
    }

    public function destroy(Format $format)
    {
        $format->dependencies()->detach();
        $format->delete();

        return redirect()->route('formats.index')->with('success', 'Formato eliminado correctamente.');
    }
}