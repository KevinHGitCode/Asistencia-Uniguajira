<?php

namespace App\Http\Controllers\Configuration;

use App\Exports\CampusExport;
use App\Exports\CampusTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CampusController extends Controller
{
    public function index()
    {
        return view('administration.campuses.index', ['totalCampuses' => Campus::count()]);
    }

    public function store(Request $request)
    {
        $name = $this->normalizedName($request->input('name', ''));
        $request->merge(['name' => $name]);
        $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('campuses', 'name')]]);

        $campus = Campus::create(['name' => $name]);
        ActivityLogService::log('crear', 'sedes', "Creó la sede '{$campus->name}'", $campus);

        return redirect()->route('campuses.index')->with('success', 'Sede creada exitosamente.');
    }

    public function update(Request $request, Campus $campus)
    {
        $name = $this->normalizedName($request->input('name', ''));
        $request->merge(['name' => $name]);
        $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('campuses', 'name')->ignore($campus->id)]]);

        $oldName = $campus->name;
        $campus->update(['name' => $name]);
        ActivityLogService::log('editar', 'sedes', "Editó la sede '{$campus->name}'", $campus, [
            'name' => ['old' => $oldName, 'new' => $campus->name],
        ]);

        return redirect()->route('campuses.index')->with('success', 'Sede actualizada exitosamente.');
    }

    public function destroy(Campus $campus)
    {
        $campus->loadCount(['users', 'events', 'dependencies', 'programs', 'areas']);
        $inUse = $campus->users_count + $campus->events_count + $campus->dependencies_count + $campus->programs_count + $campus->areas_count;

        if ($inUse > 0) {
            return redirect()->route('campuses.index')->with('error', 'No se puede eliminar una sede que tiene registros asociados.');
        }

        $name = $campus->name;
        $campus->delete();
        ActivityLogService::log('eliminar', 'sedes', "Eliminó la sede '{$name}'");

        return redirect()->route('campuses.index')->with('success', 'Sede eliminada exitosamente.');
    }

    public function importExcel(Request $request)
    {
        $request->validate(['excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']]);
        $rows = Excel::toArray([], $request->file('excel_file'))[0] ?? [];
        $headers = array_map(fn ($header) => trim((string) $header), array_values((array) ($rows[0] ?? [])));
        $nameIndex = array_search('Nombre', $headers);

        if ($nameIndex === false) {
            return back()->withErrors(['excel_file' => 'El archivo debe incluir la columna Nombre.'])->with('active_tab', 'import');
        }

        $existing = Campus::pluck('name')->map(fn ($name) => mb_strtolower($name, 'UTF-8'))->flip()->all();
        $batch = [];
        $skipped = 0;
        $now = now();
        foreach (array_slice($rows, 1) as $row) {
            $name = $this->normalizedName((string) (array_values((array) $row)[$nameIndex] ?? ''));
            $key = mb_strtolower($name, 'UTF-8');
            if ($name === '' || isset($existing[$key])) {
                $skipped++;

                continue;
            }
            $existing[$key] = true;
            $batch[] = ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('campuses')->insert($chunk);
        }

        ActivityLogService::log('importar', 'sedes', 'Importó '.count($batch).' sede(s) desde Excel');

        return redirect()->route('campuses.index')->with('success', 'Se importaron '.count($batch).' sede(s).'.($skipped ? " Se omitieron {$skipped} fila(s)." : ''));
    }

    public function downloadTemplate()
    {
        return Excel::download(new CampusTemplateExport, 'plantilla_sedes.xlsx');
    }

    public function downloadExport()
    {
        return Excel::download(new CampusExport, 'sedes.xlsx');
    }

    private function normalizedName(string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
    }
}
