<?php

namespace App\Http\Controllers\Configuration;

use App\Exports\SkippedProgramsExport;
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
            'name'         => self::normalizeName(trim($request->name)),
            'program_type' => $request->program_type ?: null,
        ]);

        return redirect()->route('programs.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function update(Request $request, Program $program)
    {
        $this->validateProgram($request, $program->id);

        $program->update([
            'name'         => self::normalizeName(trim($request->name)),
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
            return back()->withErrors(['excel_file' => 'El archivo esta vacio.']);
        }

        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: "Nombre".']);
        }

        $typeIndex = array_search('Tipo', $headerRow);

        array_shift($rows);

        // Cache: clave sin acentos y en minusculas.
        $existingSet = array_flip(
            Program::all(['name'])->map(fn ($p) => self::comparisonKey($p->name))->toArray()
        );

        $created = 0;
        $skippedRows = [];
        $batch = [];
        $now = now()->toDateTimeString();
        $typeMap = ['pregrado' => 'Pregrado', 'posgrado' => 'Posgrado'];

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $rawName = trim((string) ($values[$nameIndex] ?? ''));
            $rawType = $typeIndex !== false ? trim((string) ($values[$typeIndex] ?? '')) : '';

            if ($rawName === '') {
                $skippedRows[] = $this->skippedRow($rawName, $rawType, 'Nombre vacio');
                continue;
            }

            $programName = self::normalizeName($rawName);
            $nameKey = self::comparisonKey($programName);

            if (isset($existingSet[$nameKey])) {
                $skippedRows[] = $this->skippedRow(
                    $programName,
                    $rawType,
                    "Programa duplicado o ya existente: \"{$programName}\""
                );
                continue;
            }

            $programType = null;
            if ($rawType !== '') {
                $programType = $typeMap[mb_strtolower($rawType, 'UTF-8')] ?? null;
            }

            $existingSet[$nameKey] = true;
            $batch[] = [
                'name'         => $programName,
                'program_type' => $programType,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('programs')->insert($chunk);
        }

        session(['programs_import_skipped' => $skippedRows]);

        $skipped = count($skippedRows);
        $msg = "Se importaron {$created} programa(s) nuevos.";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacias o ya existentes).";
        }

        return redirect()->route('programs.index')
            ->with('success', $msg)
            ->with('import_result', [
                'created' => $created,
                'skipped' => $skipped,
            ]);
    }

    public function downloadSkipped()
    {
        $skipped = session('programs_import_skipped', []);

        if (empty($skipped)) {
            return redirect()->route('programs.index')
                ->with('error', 'No hay datos omitidos disponibles para descargar.');
        }

        return Excel::download(
            new SkippedProgramsExport($skipped),
            'programas_omitidos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\ProgramTemplateExport(),
            'plantilla_programas.xlsx'
        );
    }

    public function downloadExport()
    {
        return Excel::download(
            new \App\Exports\ProgramExport(),
            'programas.xlsx'
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
            'program_type.in' => 'El tipo de programa no es valido.',
        ]);
    }

    /**
     * Normaliza un nombre con soporte UTF-8 (primera letra mayuscula, resto minusculas).
     */
    public static function normalizeName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $lower = preg_replace('/\s+/u', ' ', $lower);

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_substr($lower, 1, null, 'UTF-8');
    }

    /**
     * Genera una clave de comparacion: minusculas + sin acentos + espacios normalizados.
     */
    public static function comparisonKey(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        // Colapsar multiples espacios/tabs en uno solo.
        $normalized = preg_replace('/\s+/u', ' ', $lower);

        return self::stripAccents($normalized);
    }

    /**
     * Elimina diacriticos/acentos de un string UTF-8.
     */
    public static function stripAccents(string $value): string
    {
        // Descomponer a NFD (letra + combining accent), luego quitar combining marks.
        if (class_exists(\Normalizer::class)) {
            $decomposed = \Normalizer::normalize($value, \Normalizer::FORM_D);
            if ($decomposed !== false) {
                return preg_replace('/\pM/u', '', $decomposed);
            }
        }

        // Fallback.
        if (function_exists('transliterator_transliterate')) {
            return transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC;', $value);
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $converted !== false ? $converted : $value;
    }

    private function skippedRow(string $name, string $type, string $motivo): array
    {
        return [
            'Nombre'  => $name !== '' ? $name : null,
            'Tipo'    => $type !== '' ? $type : null,
            '_motivo' => $motivo,
        ];
    }
}
