<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
        // No se usa â€” el modal estÃ¡ en index
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:areas,name',
            'dependency_id' => 'required|exists:dependencies,id',
        ], [
            'name.required'          => 'El nombre del Ã¡rea es obligatorio.',
            'name.unique'            => 'Ya existe un Ã¡rea con ese nombre.',
            'dependency_id.required' => 'Debes seleccionar una dependencia.',
            'dependency_id.exists'   => 'La dependencia seleccionada no existe.',
        ]);

        Area::create($request->only('name', 'dependency_id'));

        return redirect()->route('areas.index')
            ->with('success', 'Ãrea creada correctamente.');
    }

    public function show(Area $area)
    {
        // No se usa
    }

    public function edit(Area $area)
    {
        // No se usa â€” el modal estÃ¡ en index
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:areas,name,' . $area->id,
            'dependency_id' => 'required|exists:dependencies,id',
        ], [
            'name.required'          => 'El nombre del Ã¡rea es obligatorio.',
            'name.unique'            => 'Ya existe un Ã¡rea con ese nombre.',
            'dependency_id.required' => 'Debes seleccionar una dependencia.',
            'dependency_id.exists'   => 'La dependencia seleccionada no existe.',
        ]);

        $area->update($request->only('name', 'dependency_id'));

        return redirect()->route('areas.index')
            ->with('success', 'Ãrea actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Ãrea eliminada correctamente.');
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

        $headerRow       = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex       = array_search('Nombre', $headerRow);
        $dependencyIndex = array_search('Dependencia', $headerRow);

        $missing = [];
        if ($nameIndex === false) {
            $missing[] = '"Nombre"';
        }
        if ($dependencyIndex === false) {
            $missing[] = '"Dependencia"';
        }
        if (!empty($missing)) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene las columnas requeridas: ' . implode(', ', $missing) . '.']);
        }

        array_shift($rows);

        // Cache: clave sin acentos y en minúsculas -> true
        $existingSet = array_flip(
            Area::all(['name'])->map(fn ($a) => self::comparisonKey($a->name))->toArray()
        );

        $dependencyMap = Dependency::all(['id', 'name'])
            ->mapWithKeys(fn ($d) => [self::comparisonKey($d->name) => $d->id])
            ->toArray();

        $created = 0;
        $skipped = 0;
        $batch   = [];
        $now     = now()->toDateTimeString();

        foreach ($rows as $row) {
            $values        = array_values((array) $row);
            $rawName       = trim((string) ($values[$nameIndex] ?? ''));
            $rawDependency = trim((string) ($values[$dependencyIndex] ?? ''));

            if ($rawName == '' || $rawDependency == '') {
                $skipped++;
                continue;
            }

            $normalized = self::normalizeName($rawName);
            $nameKey    = self::comparisonKey($normalized);

            if (isset($existingSet[$nameKey])) {
                $skipped++;
                continue;
            }

            $dependencyKey = self::comparisonKey($rawDependency);
            $dependencyId  = $dependencyMap[$dependencyKey] ?? null;

            if (!$dependencyId) {
                $skipped++;
                continue;
            }

            $existingSet[$nameKey] = true;
            $batch[] = [
                'name'          => $normalized,
                'dependency_id' => $dependencyId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('areas')->insert($chunk);
        }

        $msg = "Se importaron {$created} área(s) nuevas.";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacías, duplicadas o con dependencia inexistente).";
        }

        return redirect()->route('areas.index')->with('success', $msg);
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\AreaTemplateExport(),
            'plantilla_areas.xlsx'
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
                return preg_replace('/\\pM/u', '', $decomposed);
            }
        }

        if (function_exists('transliterator_transliterate')) {
            return transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC;', $value);
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }
}


