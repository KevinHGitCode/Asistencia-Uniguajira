<?php

namespace App\Http\Controllers\Configuration;

use App\Exports\SkippedProgramsExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\Campus;
use App\Models\Program;
use App\Services\ActivityLogService;
use App\Services\CampusNameResolver;
use App\Services\CampusScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ProgramController extends Controller
{
    public function index(Request $request, CampusScopeService $campusScope)
    {
        $campusScope->syncSelectedCampusFromRequest($request);

        $totalPrograms = $campusScope->applyToQuery(Program::query(), $request->user())->count();
        $campuses = Campus::orderBy('name')->pluck('name', 'id')->toArray();
        $academicPrograms = AcademicProgram::orderBy('name')->pluck('name', 'id')->toArray();
        $activeCampusId = $campusScope->activeCampusId($request->user());
        $isSuperadmin = $request->user()?->isSuperadmin() ?? false;

        return view('administration.programs.index', compact(
            'totalPrograms',
            'campuses',
            'academicPrograms',
            'activeCampusId',
            'isSuperadmin'
        ));
    }

    public function store(Request $request, CampusScopeService $campusScope)
    {
        $campusId = $this->campusIdForWrite($request, $campusScope);
        $academicProgram = $this->resolveAcademicProgram($request);
        $this->ensureProgramOfferingIsUnique($academicProgram->id, $campusId, $request->offer_location);
        $campus = Campus::findOrFail($campusId);

        $program = Program::create([
            'name' => $this->compatibleProgramName($academicProgram->name, $campus->name, $request->offer_location),
            'program_type' => $request->program_type ?: null,
            'campus_id' => $campusId,
            'offer_location' => $request->offer_location ?: null,
            'academic_program_id' => $academicProgram->id,
        ]);

        ActivityLogService::log('crear', 'programas', "Creó el programa '{$program->name}'", $program);

        return redirect()->route('programs.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    public function update(Request $request, Program $program, CampusScopeService $campusScope)
    {
        $campusScope->authorizeResource($request->user(), $program);
        $campusId = $this->campusIdForWrite($request, $campusScope, (int) $program->campus_id);
        $academicProgram = $this->resolveAcademicProgram($request, $program->academic_program_id);
        $this->ensureProgramOfferingIsUnique($academicProgram->id, $campusId, $request->offer_location, $program->id);
        $campus = Campus::findOrFail($campusId);

        $original = $program->only(['name', 'program_type', 'campus_id', 'offer_location', 'academic_program_id']);

        $program->update([
            'name' => $this->compatibleProgramName($academicProgram->name, $campus->name, $request->offer_location),
            'program_type' => $request->program_type ?: null,
            'campus_id' => $campusId,
            'offer_location' => $request->offer_location ?: null,
            'academic_program_id' => $academicProgram->id,
        ]);

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $program->$field;
            if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }

        ActivityLogService::log('editar', 'programas', "Editó el programa '{$program->name}'", $program, $changes);

        return redirect()->route('programs.index')
            ->with('success', 'Programa actualizado exitosamente.');
    }

    public function destroy(Request $request, Program $program, CampusScopeService $campusScope)
    {
        $campusScope->authorizeResource($request->user(), $program);

        $program->loadCount('participants');
        $count = $program->participants_count;

        if ($count > 0) {
            return redirect()->route('programs.index')
                ->with('error', "No se puede eliminar \"{$program->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $program->name;
        $program->delete();

        ActivityLogService::log('eliminar', 'programas', "Eliminó el programa '{$name}'");

        return redirect()->route('programs.index')
            ->with('success', "Programa \"{$name}\" eliminado exitosamente.");
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
            return back()->withErrors(['excel_file' => 'El archivo esta vacio.']);
        }

        $headerRow = array_map(fn ($h) => trim((string) ($h ?? '')), array_values((array) $rows[0]));
        $nameIndex = array_search('Nombre', $headerRow);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene la columna requerida: "Nombre".']);
        }

        $typeIndex = array_search('Tipo', $headerRow);

        array_shift($rows);

        $user = $request->user();
        $isSuperadmin = $user?->isSuperadmin() ?? false;
        $fixedCampusId = $isSuperadmin ? null : $this->campusIdForWrite($request, $campusScope);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);
        $fixedCampus = $fixedCampusId ? $campuses->firstWhere('id', $fixedCampusId) : null;
        $campusResolver = app(CampusNameResolver::class);

        $existingSet = Program::query()
            ->whereNotNull('academic_program_id')
            ->get(['campus_id', 'academic_program_id', 'offer_location'])
            ->mapWithKeys(fn (Program $program) => [$this->offeringKey($program->campus_id, $program->academic_program_id, $program->offer_location) => true])
            ->toArray();

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

            $detectedCampus = $campusResolver->resolve($rawName, $campuses);
            if (! $detectedCampus) {
                $skippedRows[] = $this->skippedRow(
                    $rawName,
                    $rawType,
                    'No se indicó una sede válida. Agrega al final "- Sede" (por ejemplo, "- Maicao").'
                );

                continue;
            }

            if (! $isSuperadmin && $detectedCampus && $detectedCampus->id !== $fixedCampusId) {
                $skippedRows[] = $this->skippedRow(
                    $rawName,
                    $rawType,
                    "La sede indicada ({$detectedCampus->name}) no corresponde a tu sede ({$fixedCampus?->name})."
                );

                continue;
            }

            $targetCampus = $isSuperadmin ? $detectedCampus : $fixedCampus;
            $offerLocation = null;
            $academicName = self::normalizeName($campusResolver->withoutSuffix($rawName));
            $academicProgram = $this->findOrCreateAcademicProgram($academicName);

            $programType = null;
            if ($rawType !== '') {
                $programType = $typeMap[mb_strtolower($rawType, 'UTF-8')] ?? null;
            }

            $programKey = $this->offeringKey($targetCampus->id, $academicProgram->id, $offerLocation);
            if (isset($existingSet[$programKey])) {
                $skippedRows[] = $this->skippedRow(
                    $academicName,
                    $rawType,
                    "Programa ya existe para la sede {$targetCampus->name}: \"{$academicName}\""
                );

                continue;
            }

            $existingSet[$programKey] = true;
            $batch[] = [
                'name' => $this->compatibleProgramName($academicProgram->name, $targetCampus->name, $offerLocation),
                'program_type' => $programType,
                'campus_id' => $targetCampus->id,
                'offer_location' => $offerLocation,
                'academic_program_id' => $academicProgram->id,
                'created_at' => $now,
                'updated_at' => $now,
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

        ActivityLogService::log('importar', 'programas', "Importó {$created} programa(s) desde Excel", metadata: ['created' => $created, 'skipped' => $skipped]);

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
            'programas_omitidos_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function downloadTemplate()
    {
        ActivityLogService::log('exportar', 'programas', 'Descargó la plantilla de importación de programas');

        return Excel::download(
            new \App\Exports\ProgramTemplateExport,
            'plantilla_programas.xlsx'
        );
    }

    public function downloadExport(Request $request, CampusScopeService $campusScope)
    {
        ActivityLogService::log('exportar', 'programas', 'Descargó el listado de programas en Excel');

        return Excel::download(
            new \App\Exports\ProgramExport($campusScope->activeCampusId($request->user())),
            'programas.xlsx'
        );
    }

    private function validateProgram(Request $request): void
    {
        $request->validate([
            'academic_program_id' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'name' => ['nullable', 'required_without:academic_program_id', 'string', 'max:200'],
            'program_type' => ['nullable', Rule::in(['Pregrado', 'Posgrado'])],
            'offer_location' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required_without' => 'Selecciona un programa académico o escribe el nombre del nuevo programa.',
            'name.max' => 'El nombre no puede superar los 200 caracteres.',
            'program_type.in' => 'El tipo de programa no es valido.',
        ]);
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
                'campus_id' => 'Selecciona una sede antes de crear o importar programas.',
            ]);
        }

        return (int) $campusId;
    }

    private function resolveAcademicProgram(Request $request, ?int $fallbackAcademicProgramId = null): AcademicProgram
    {
        $this->validateProgram($request);

        $academicProgramId = $request->integer('academic_program_id') ?: $fallbackAcademicProgramId;

        if ($academicProgramId) {
            return AcademicProgram::findOrFail($academicProgramId);
        }

        $academicName = $this->baseAcademicProgramName(self::normalizeName((string) $request->name));

        if ($academicName === '') {
            throw ValidationException::withMessages([
                'name' => 'Escribe el nombre del programa académico.',
            ]);
        }

        return $this->findOrCreateAcademicProgram($academicName);
    }

    private function findOrCreateAcademicProgram(string $name): AcademicProgram
    {
        $key = self::comparisonKey($name);
        $existing = AcademicProgram::all(['id', 'name'])
            ->first(fn (AcademicProgram $program) => self::comparisonKey($program->name) === $key);

        if ($existing) {
            return $existing;
        }

        return AcademicProgram::create(['name' => $name]);
    }

    private function ensureProgramOfferingIsUnique(int $academicProgramId, int $campusId, ?string $offerLocation = null, ?int $ignoreProgramId = null): void
    {
        $exists = Program::query()
            ->where('campus_id', $campusId)
            ->where('academic_program_id', $academicProgramId)
            ->where('offer_location', $offerLocation ?: null)
            ->when($ignoreProgramId, fn ($query) => $query->where('id', '<>', $ignoreProgramId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'academic_program_id' => 'Este programa académico ya está registrado para la sede seleccionada.',
            ]);
        }
    }

    private function compatibleProgramName(string $academicProgramName, string $campusName, ?string $offerLocation = null): string
    {
        $name = self::normalizeName($academicProgramName);
        $suffix = ' - '.($offerLocation ?: $campusName);

        if (mb_strtolower(mb_substr($name, -mb_strlen($suffix)), 'UTF-8') === mb_strtolower($suffix, 'UTF-8')) {
            return $name;
        }

        return "{$name}{$suffix}";
    }

    private function offeringKey(int $campusId, int $academicProgramId, ?string $offerLocation): string
    {
        return "{$campusId}:{$academicProgramId}:".self::comparisonKey($offerLocation ?? '');
    }

    private function baseAcademicProgramName(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        $campusNames = Campus::orderBy('name')->pluck('name')->all();
        foreach ($campusNames as $campusName) {
            $suffix = " - {$campusName}";
            if (mb_strtolower(mb_substr($normalized, -mb_strlen($suffix)), 'UTF-8') === mb_strtolower($suffix, 'UTF-8')) {
                return trim(mb_substr($normalized, 0, mb_strlen($normalized) - mb_strlen($suffix), 'UTF-8'));
            }
        }

        return $normalized;
    }

    /**
     * Normaliza un nombre con soporte UTF-8 (primera letra mayuscula, resto minusculas).
     */
    public static function normalizeName(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $lower = preg_replace('/\s+/u', ' ', $lower);

        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
             .mb_substr($lower, 1, null, 'UTF-8');
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
            'Nombre' => $name !== '' ? $name : null,
            'Tipo' => $type !== '' ? $type : null,
            '_motivo' => $motivo,
        ];
    }
}
