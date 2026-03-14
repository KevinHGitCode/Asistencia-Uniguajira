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
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique'   => 'Ya existe un formato con ese identificador.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max'   => 'El archivo no debe superar los 5MB.',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('pdf_file')) {
            $fileName = $request->file('pdf_file')->getClientOriginalName();
            $request->file('pdf_file')->move(public_path('formats'), $fileName);
            $data['file'] = $fileName;
        }

        $format = Format::create($data);

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
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique'   => 'Ya existe un formato con ese identificador.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max'   => 'El archivo no debe superar los 5MB.',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('pdf_file')) {
            // Eliminar archivo anterior si existe
            if ($format->file && file_exists(public_path("formats/{$format->file}"))) {
                unlink(public_path("formats/{$format->file}"));
            }

            $fileName = $request->file('pdf_file')->getClientOriginalName();
            $request->file('pdf_file')->move(public_path('formats'), $fileName);
            $data['file'] = $fileName;
        }

        $format->update($data);
        $format->dependencies()->sync($request->dependencies ?? []);

        return redirect()->route('formats.index')->with('success', 'Formato actualizado correctamente.');
    }

    public function destroy(Format $format)
    {
        if ($format->file && file_exists(public_path("formats/{$format->file}"))) {
            unlink(public_path("formats/{$format->file}"));
        }

        $format->dependencies()->detach();
        $format->delete();

        return redirect()->route('formats.index')->with('success', 'Formato eliminado correctamente.');
    }
}