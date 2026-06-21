<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Dependency;
use App\Services\ActivityLogService;
use App\Services\CampusNameResolver;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DependencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CampusScopeService $campusScope)
    {
        $campusScope->syncSelectedCampusFromRequest($request);

        $totalDependencies = $campusScope->applyToQuery(Dependency::query(), $request->user())->count();
        $campuses = Campus::orderBy('name')->pluck('name', 'id')->toArray();
        $activeCampusId = $campusScope->activeCampusId($request->user());
        $isSuperadmin = $request->user()?->isSuperadmin() ?? false;

        return view('administration.dependencies.index', compact(
            'totalDependencies',
            'campuses',
            'activeCampusId',
            'isSuperadmin'
        ));
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
    public function store(Request $request, CampusScopeService $campusScope)
    {
        $campusId = $this->campusIdForWrite($request, $campusScope);
        $normalizedName = self::normalizeName(
            self::normalizeExcelText($request->name)
        );
        $request->merge(['name' => $normalizedName]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('dependencies', 'name')],
        ], [
            'name.required' => 'El nombre de la dependencia es obligatorio.',
            'name.unique' => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependency = Dependency::create([
            'name' => $normalizedName,
            'campus_id' => $campusId,
        ]);

        $formatGeneral = \App\Models\Format::where('slug', 'general')->first();
        if ($formatGeneral) {
            $dependency->formats()->attach($formatGeneral->id);
        }

        ActivityLogService::log('crear', 'dependencias', "Creó la dependencia '{$dependency->name}'", $dependency);

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
    public function update(Request $request, Dependency $dependency, CampusScopeService $campusScope)
    {
        $campusScope->authorizeResource($request->user(), $dependency);
        $campusId = $this->campusIdForWrite($request, $campusScope, (int) $dependency->campus_id);
        $normalizedName = self::normalizeName(
            self::normalizeExcelText($request->name)
        );
        $request->merge(['name' => $normalizedName]);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('dependencies', 'name')->ignore($dependency->id)],
        ], [
            'name.required' => 'El nombre de la dependencia es obligatorio.',
            'name.unique' => 'Ya existe una dependencia con ese nombre.',
        ]);

        $oldName = $dependency->name;
        $oldCampusId = $dependency->campus_id;
        $dependency->update([
            'name' => $normalizedName,
            'campus_id' => $campusId,
        ]);

        $changes = [];
        if ($oldName !== $dependency->name) {
            $changes['name'] = ['old' => $oldName, 'new' => $dependency->name];
        }
        if ((int) $oldCampusId !== (int) $dependency->campus_id) {
            $changes['campus_id'] = ['old' => $oldCampusId, 'new' => $dependency->campus_id];
        }

        ActivityLogService::log('editar', 'dependencias', "Editó la dependencia '{$dependency->name}'", $dependency, $changes);

        return redirect()->route('dependencies.index')->with('success', 'Dependencia actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Dependency $dependency, CampusScopeService $campusScope)
    {
        $campusScope->authorizeResource($request->user(), $dependency);

        $name = $dependency->name;
        $dependency->delete();

        ActivityLogService::log('eliminar', 'dependencias', "Eliminó la dependencia '{$name}'");

        return redirect()->route('dependencies.index')->with('success', 'Dependencia eliminada exitosamente.');
    }

    public function importExcel(Request $request, CampusScopeService $campusScope)
    {
        set_time_limit(0);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.mimes' => 'El archivo debe ser .xlsx, .xls o .csv.',
            'excel_file.max' => 'El archivo no debe superar los 10 MB.',
        ]);

        $sheets = Excel::toArray([], $request->file('excel_file'));
        $rows = $sheets[0] ?? [];

        if (empty($rows)) {
            return back()->withErrors(['excel_file' => 'El archivo está vacío.']);
        }

        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: «Nombre».']);
        }

        array_shift($rows);

        $user = $request->user();
        $isSuperadmin = $user?->isSuperadmin() ?? false;
        $fixedCampusId = $isSuperadmin ? null : $this->campusIdForWrite($request, $campusScope);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);
        $fixedCampus = $fixedCampusId ? $campuses->firstWhere('id', $fixedCampusId) : null;
        $campusResolver = app(CampusNameResolver::class);

        // Cache: clave sin acentos y en minúsculas → true
        $existingSet = array_flip(
            Dependency::all(['name'])->map(fn ($d) => self::comparisonKey($d->name))->toArray()
        );

        $created = 0;
        $skippedRows = [];
        $batch = [];
        $createdNames = [];
        $now = now()->toDateTimeString();

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $rawName = self::normalizeExcelText($values[$nameIndex] ?? null);

            if ($rawName === '') {
                $skippedRows[] = $this->skippedRow($rawName, 'Nombre vacío');

                continue;
            }

            $detectedCampus = $campusResolver->resolve($rawName, $campuses);
            if ($isSuperadmin && ! $detectedCampus && mb_strtolower((string) $campusResolver->suffix($rawName), 'UTF-8') === 'manaure') {
                $detectedCampus = Campus::firstOrCreate(['name' => 'Manaure']);
                $campuses->push($detectedCampus);
            }

            if ($isSuperadmin && ! $detectedCampus) {
                $detectedCampus = $campusResolver->resolveMentioned($rawName, $campuses)
                    ?? $campuses->first(fn (Campus $campus) => mb_strtolower($campus->name, 'UTF-8') === 'riohacha');

                if (! $detectedCampus) {
                    $skippedRows[] = $this->skippedRow($rawName, 'No existe la sede predeterminada Riohacha.');

                    continue;
                }
            }

            if (! $isSuperadmin && $detectedCampus && $detectedCampus->id !== $fixedCampusId) {
                $skippedRows[] = $this->skippedRow(
                    $rawName,
                    "La sede indicada ({$detectedCampus->name}) no corresponde a tu sede ({$fixedCampus?->name})."
                );

                continue;
            }

            $targetCampusId = $isSuperadmin ? $detectedCampus->id : $fixedCampusId;

            $normalized = self::normalizeName($rawName);
            $nameKey = self::comparisonKey($normalized);

            if (isset($existingSet[$nameKey])) {
                $skippedRows[] = $this->skippedRow($rawName, 'Dependencia duplicada o ya existente.');

                continue;
            }

            $existingSet[$nameKey] = true;
            $batch[] = [
                'name' => $normalized,
                'campus_id' => $targetCampusId,
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
                    'format_id' => $formatGeneral->id,
                ])->toArray();

                foreach (array_chunk($pivot, 500) as $chunk) {
                    DB::table('dependency_format')->insertOrIgnore($chunk);
                }
            }
        }

        session(['dependencies_import_skipped' => $skippedRows]);

        $skipped = count($skippedRows);
        $msg = "Se importaron {$created} dependencia(s) nuevas.";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s). Puedes descargar el reporte con los motivos.";
        }

        ActivityLogService::log('importar', 'dependencias', "Importó {$created} dependencia(s) desde Excel", metadata: ['created' => $created, 'skipped' => $skipped]);

        return redirect()->route('dependencies.index')
            ->with('success', $msg)
            ->with('import_result', ['created' => $created, 'skipped' => $skipped]);
    }

    public function downloadSkipped()
    {
        $skipped = session('dependencies_import_skipped', []);

        if (empty($skipped)) {
            return redirect()->route('dependencies.index')
                ->with('error', 'No hay datos omitidos disponibles para descargar.');
        }

        return Excel::download(
            new \App\Exports\SkippedDependenciesExport($skipped),
            'dependencias_omitidas_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        ActivityLogService::log('exportar', 'dependencias', 'Descargó la plantilla de importación de dependencias');

        return Excel::download(
            new \App\Exports\DependencyTemplateExport,
            'plantilla_dependencias.xlsx'
        );
    }

    public function downloadExport(Request $request, CampusScopeService $campusScope)
    {
        ActivityLogService::log('exportar', 'dependencias', 'Descargó el listado de dependencias en Excel');

        return Excel::download(
            new \App\Exports\DependencyExport($campusScope->activeCampusId($request->user())),
            'dependencias.xlsx'
        );
    }

    private function campusIdForWrite(Request $request, CampusScopeService $campusScope, ?int $fallbackCampusId = null): int
    {
        $user = $request->user();

        if (! $user?->isSuperadmin()) {
            if (! $user?->campus_id) {
                throw ValidationException::withMessages([
                    'campus_id' => 'Tu usuario no tiene sede asignada para crear registros.',
                ]);
            }

            return (int) $user->campus_id;
        }

        $validated = $request->validate([
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
        ]);

        $campusId = $validated['campus_id'] ?? $fallbackCampusId ?? $campusScope->activeCampusId($user);

        if (! $campusId) {
            throw ValidationException::withMessages([
                'campus_id' => 'Selecciona una sede antes de crear o importar dependencias.',
            ]);
        }

        return (int) $campusId;
    }

    /**
     * Normaliza un nombre con soporte UTF-8 (primera letra mayúscula, resto minúsculas).
     * Pública para que pueda reutilizarse desde otros cargues masivos (por ejemplo,
     * cuando el import de participantes crea dependencias a partir de la columna
     * "Programa o Dependencia").
     */
    public static function normalizeName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $lower = preg_replace('/\s+/u', ' ', $lower);

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
             .mb_substr($lower, 1, null, 'UTF-8');
    }

    /**
     * Genera una clave de comparación: minúsculas + sin acentos.
     */
    private static function comparisonKey(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $lower = preg_replace('/\s+/u', ' ', $lower);

        return self::stripAccents($lower);
    }

    /**
     * Normaliza texto crudo proveniente de Excel y corrige mojibake frecuente.
     */
    private static function normalizeExcelText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = strtr($text, [
            'Ã¡' => 'á',
            'Ã©' => 'é',
            'Ã­' => 'í',
            'Ã³' => 'ó',
            'Ãº' => 'ú',
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã' => 'Í',
            'Ã“' => 'Ó',
            'Ãš' => 'Ú',
            'Ã±' => 'ñ',
            'Ã‘' => 'Ñ',
            'Ã¼' => 'ü',
            'Ãœ' => 'Ü',
            'Â' => '',
        ]);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
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

    private function skippedRow(string $name, string $motivo): array
    {
        return ['Nombre' => $name !== '' ? $name : null, '_motivo' => $motivo];
    }
}
