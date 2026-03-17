<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
            return back()->withErrors(['excel_file' => 'El archivo está vacío.'])->with('active_tab', 'import');
        }

        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: «Nombre».'])->with('active_tab', 'import');
        }

        array_shift($rows);

        $existingSet = array_flip(
            Affiliation::all(['name'])->map(fn ($a) => self::comparisonKey($a->name))->toArray()
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

            $normalized = self::normalizeName($rawName);
            $nameKey    = self::comparisonKey($normalized);

            if (isset($existingSet[$nameKey])) {
                $skipped++;
                continue;
            }

            $existingSet[$nameKey] = true;
            $batch[] = ['name' => $normalized, 'created_at' => $now, 'updated_at' => $now];
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('affiliations')->insert($chunk);
        }

        $msg = "Se importaron {$created} afiliación(es) nueva(s).";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacías o ya existentes).";
        }

        return redirect()->route('affiliations.index')->with('success', $msg);
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\AffiliationTemplateExport(),
            'plantilla_afiliaciones.xlsx'
        );
    }

    public function downloadExport()
    {
        return Excel::download(
            new \App\Exports\AffiliationExport(),
            'afiliaciones.xlsx'
        );
    }

    private static function normalizeName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_substr($lower, 1, null, 'UTF-8');
    }

    private static function comparisonKey(string $value): string
    {
        return self::stripAccents(mb_strtolower(trim($value), 'UTF-8'));
    }

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
