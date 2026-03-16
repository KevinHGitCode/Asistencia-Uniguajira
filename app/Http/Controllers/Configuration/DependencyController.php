<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DependencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // obtener dependencias con sus eventos relacionada
        $dependencies = Dependency::with('areas')
            ->withCount(['areas', 'events'])
            ->get();

        return view('administration.dependencies.index', compact('dependencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:dependencies,name',
        ], [
            'name.required'         => 'El nombre de la dependencia es obligatorio.',
            'name.unique'           => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependency = Dependency::create([
            'name' => $request->name,
        ]);

        $formatGeneral = \App\Models\Format::where('slug', 'general')->first();
        if ($formatGeneral) {
            $dependency->formats()->attach($formatGeneral->id);
        }

        return redirect()->route('dependencies.index')->with('success', 'Dependencia creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dependency $dependency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dependency $dependency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dependency $dependency)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:dependencies,name,' . $dependency->id,
        ], [
            'name.required'         => 'El nombre de la dependencia es obligatorio.',
            'name.unique'           => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependency->update([
            'name' => $request->name,
        ]);

        return redirect()->route('dependencies.index')->with('success', 'Dependencia actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dependency $dependency)
    {
        $dependency->delete();

        return redirect()->route('dependencies.index')->with('success', 'Dependencia eliminada exitosamente.');
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

        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: «Nombre».']);
        }

        array_shift($rows);

        // Cache: clave sin acentos y en minúsculas → true
        $existingSet = array_flip(
            Dependency::all(['name'])->map(fn ($d) => self::comparisonKey($d->name))->toArray()
        );

        $created       = 0;
        $skipped       = 0;
        $batch         = [];
        $createdNames  = [];
        $now           = now()->toDateTimeString();

        foreach ($rows as $row) {
            $values  = array_values((array) $row);
            $rawName = trim((string) ($values[$nameIndex] ?? ''));

            if ($rawName === '') {
                $skipped++;
                continue;
            }

            $normalized = self::normalizeName($rawName);
            $nameKey    = self::comparisonKey($normalized);

            if (isset($existingSet[$nameKey])) {
                $skipped++;
                continue;
            }

            $existingSet[$nameKey] = true;
            $batch[] = [
                'name'       => $normalized,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $createdNames[] = $normalized;
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('dependencies')->insert($chunk);
        }

        if ($created > 0) {
            $formatGeneral = \App\Models\Format::where('slug', 'general')->first();
            if ($formatGeneral) {
                $newDeps = Dependency::whereIn('name', $createdNames)->get(['id']);
                $pivot = $newDeps->map(fn ($dep) => [
                    'dependency_id' => $dep->id,
                    'format_id'     => $formatGeneral->id,
                ])->toArray();

                foreach (array_chunk($pivot, 500) as $chunk) {
                    DB::table('dependency_format')->insertOrIgnore($chunk);
                }
            }
        }

        $msg = "Se importaron {$created} dependencia(s) nuevas.";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacías o ya existentes).";
        }

        return redirect()->route('dependencies.index')->with('success', $msg);
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\DependencyTemplateExport(),
            'plantilla_dependencias.xlsx'
        );
    }

    /**
     * Normaliza un nombre con soporte UTF-8 (primera letra mayúscula, resto minúsculas).
     */
    private static function normalizeName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_substr($lower, 1, null, 'UTF-8');
    }

    /**
     * Genera una clave de comparación: minúsculas + sin acentos.
     */
    private static function comparisonKey(string $value): string
    {
        return self::stripAccents(mb_strtolower(trim($value), 'UTF-8'));
    }

    /**
     * Elimina diacríticos/acentos de un string UTF-8.
     */
    private static function stripAccents(string $value): string
    {
        if (class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($value, \Normalizer::FORM_D);
            if ($decomposed !== false) {
                return preg_replace('/\pM/u', '', $decomposed);
            }
        }

        if (function_exists('transliterator_transliterate')) {
            return transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC;', $value);
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }
}
