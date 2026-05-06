<?php

namespace App\Http\Controllers\Configuration;

use App\Exports\SkippedOrganizationsExport;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\ParticipantRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount('participantRoles')
            ->orderBy('name')
            ->get();

        return view('administration.organizations.index', compact('organizations'));
    }

    public function store(Request $request)
    {
        $this->validateOrganization($request);

        Organization::create([
            'name' => self::normalizeName(trim($request->name)),
        ]);

        return redirect()->route('organizations.index')
            ->with('success', 'Organización creada exitosamente.');
    }

    public function update(Request $request, Organization $organization)
    {
        $this->validateOrganization($request, $organization->id);

        $organization->update([
            'name' => self::normalizeName(trim($request->name)),
        ]);

        return redirect()->route('organizations.index')
            ->with('success', 'Organización actualizada exitosamente.');
    }

    public function destroy(Organization $organization)
    {
        $organization->loadCount('participantRoles');
        $count = $organization->participant_roles_count;

        if ($count > 0) {
            return redirect()->route('organizations.index')
                ->with('error', "No se puede eliminar \"{$organization->name}\" porque tiene {$count} participante(s) asignado(s).");
        }

        $name = $organization->name;
        $organization->delete();

        return redirect()->route('organizations.index')
            ->with('success', "Organización \"{$name}\" eliminada exitosamente.");
    }

    public function merge(Request $request, Organization $organization)
    {
        $request->validate([
            'canonical_id' => 'required|integer|exists:organizations,id|different:organization',
        ], [
            'canonical_id.required' => 'Selecciona la organización destino.',
            'canonical_id.exists'   => 'La organización destino no existe.',
            'canonical_id.different' => 'La organización destino debe ser diferente.',
        ]);

        $canonicalId = $request->canonical_id;

        ParticipantRole::where('organization_id', $organization->id)
            ->update(['organization_id' => $canonicalId]);

        $name = $organization->name;
        $organization->delete();

        return redirect()->route('organizations.index')
            ->with('success', "Organización \"{$name}\" fusionada exitosamente.");
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

        array_shift($rows);

        // Cache: clave sin acentos y en minusculas.
        $existingSet = array_flip(
            Organization::all(['name'])->map(fn ($o) => self::comparisonKey($o->name))->toArray()
        );

        $created = 0;
        $skippedRows = [];
        $batch = [];
        $now = now()->toDateTimeString();

        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $rawName = trim((string) ($values[$nameIndex] ?? ''));

            if ($rawName === '') {
                $skippedRows[] = $this->skippedRow($rawName, 'Nombre vacio');
                continue;
            }

            $orgName = self::normalizeName($rawName);
            $nameKey = self::comparisonKey($orgName);

            if (isset($existingSet[$nameKey])) {
                $skippedRows[] = $this->skippedRow(
                    $orgName,
                    "Organización duplicada o ya existente: \"{$orgName}\""
                );
                continue;
            }

            $existingSet[$nameKey] = true;
            $batch[] = [
                'name'       => $orgName,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $created++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('organizations')->insert($chunk);
        }

        session(['organizations_import_skipped' => $skippedRows]);

        $skipped = count($skippedRows);
        $msg = "Se importaron {$created} organización(es) nueva(s).";
        if ($skipped > 0) {
            $msg .= " Se omitieron {$skipped} fila(s) (vacias o ya existentes).";
        }

        return redirect()->route('organizations.index')
            ->with('success', $msg)
            ->with('import_result', [
                'created' => $created,
                'skipped' => $skipped,
            ]);
    }

    public function downloadSkipped()
    {
        $skipped = session('organizations_import_skipped', []);

        if (empty($skipped)) {
            return redirect()->route('organizations.index')
                ->with('error', 'No hay datos omitidos disponibles para descargar.');
        }

        return Excel::download(
            new SkippedOrganizationsExport($skipped),
            'organizaciones_omitidas_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\OrganizationTemplateExport(),
            'plantilla_organizaciones.xlsx'
        );
    }

    public function downloadExport()
    {
        return Excel::download(
            new \App\Exports\OrganizationExport(),
            'organizaciones.xlsx'
        );
    }

    private function validateOrganization(Request $request, ?int $ignoreId = null): void
    {
        $uniqueRule = Rule::unique('organizations', 'name');

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:150', $uniqueRule],
        ], [
            'name.required' => 'El nombre de la organización es obligatorio.',
            'name.unique'   => 'Ya existe una organización con ese nombre.',
            'name.max'      => 'El nombre no puede superar los 150 caracteres.',
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
        $normalized = preg_replace('/\s+/u', ' ', $lower);

        return self::stripAccents($normalized);
    }

    /**
     * Elimina diacriticos/acentos de un string UTF-8.
     */
    public static function stripAccents(string $value): string
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
        return [
            'Nombre'  => $name !== '' ? $name : null,
            '_motivo' => $motivo,
        ];
    }
}
