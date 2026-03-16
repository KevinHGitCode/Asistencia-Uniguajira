<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('participants')
            ->orderBy('name')
            ->get();

        return view('administration.programs.index', compact('programs'));
    }

    public function store(Request $request)
    {
        $this->validateProgram($request);

        Program::create([
            'name'         => ucfirst(strtolower(trim($request->name))),
            'program_type' => $request->program_type ?: null,
        ]);

        return redirect()->route('programs.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function update(Request $request, Program $program)
    {
        $this->validateProgram($request, $program->id);

        $program->update([
            'name'         => ucfirst(strtolower(trim($request->name))),
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

    public function importExcel(Request $request)
    {
        set_time_limit(0);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
            'excel_file.max'      => 'El archivo no debe superar los 10 MB.',
        ]);

        $sheets = Excel::toArray([], $request->file('excel_file'));
        $rows   = $sheets[0] ?? [];

        if (empty($rows)) {
            return back()->withErrors(['excel_file' => 'El archivo está vacío.']);
        }

        // Leer cabeceras
        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: «Nombre».']);
        }

        $typeIndex = array_search('Tipo', $headerRow);

        array_shift($rows);

        // Cache de nombres normalizados ya en BD
        $existingSet = array_flip(
            Program::all(['name'])->map(fn ($p) => strtolower(trim($p->name)))->toArray()
        );

        $created = 0;
        $skipped = 0;
        $batch   = [];
        $now     = now()->toDateTimeString();

        foreach ($rows as $row) {
            $values  = array_values((array) $row);
            $rawName = trim((string) ($values[$nameIndex] ?? ''));

            if ($rawName === '') {
                $skipped++;
                continue;
            }

            $normalized = ucfirst(strtolower($rawName));
            $nameKey    = strtolower($normalized);

            if (isset($existingSet[$nameKey])) {
                $skipped++;
                continue;
            }

            $typeMap     = ['pregrado' => 'Pregrado', 'posgrado' => 'Posgrado'];
            $programType = null;
            if ($typeIndex !== false) {
                $rawType     = trim((string) ($values[$typeIndex] ?? ''));
                $programType = $typeMap[strtolower($rawType)] ?? null;
            }

            $existingSet[$nameKey] = true; // evitar duplicados en el mismo archivo
            $batch[] = [
                'name'         => $normalized,
                'program_type' => $programType,
                'campus'       => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('programs')->insert($chunk);
        }

        $msg = "Se importaron {$created} programa(s) nuevos.";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacías o ya existentes).";
        }

        return redirect()->route('programs.index')->with('success', $msg);
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\ProgramTemplateExport(),
            'plantilla_programas.xlsx'
        );
    }

    private function validateProgram(Request $request, ?int $ignoreId = null): void
    {
        $uniqueRule = Rule::unique('programs', 'name');

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        $request->validate([
            'name'         => ['required', 'string', 'max:200', $uniqueRule],
            'program_type' => ['nullable', Rule::in(['Pregrado', 'Posgrado'])],
        ], [
            'name.required' => 'El nombre del programa es obligatorio.',
            'name.unique'   => 'Ya existe un programa con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 200 caracteres.',
            'program_type.in' => 'El tipo de programa no es válido.',
        ]);
    }
}
