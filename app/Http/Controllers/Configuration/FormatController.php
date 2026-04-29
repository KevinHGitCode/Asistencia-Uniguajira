<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Format;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

    // public function create()
    // {
    //     return redirect()->route('formats.index');
    // }

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
            $extension = $request->file('pdf_file')->getClientOriginalExtension();
            $fileName = $request->slug . '_' . time() . '.' . $extension;
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
            if ($format->file && file_exists(public_path("formats/{$format->file}"))) {
                unlink(public_path("formats/{$format->file}"));
            }

            $extension = $request->file('pdf_file')->getClientOriginalExtension();
            $fileName = $request->slug . '_' . time() . '.' . $extension;
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

        // Eliminar del config
        $this->removeFromConfigFile($format->slug);

        $format->dependencies()->detach();
        $format->delete();

        return redirect()->route('formats.index')->with('success', 'Formato eliminado correctamente.');
    }

    public function mapper(Format $format)
    {
        $existingMapping = config("attendance_formats.{$format->slug}") ?? $format->mapping ?? null;

        return view('administration.formats.mapper', compact('format', 'existingMapping'));
    }

    public function saveMapping(Request $request, Format $format)
    {
        $request->validate([
            'mapping'             => 'required|array',
            'mapping.file'        => 'required|string',
            'mapping.startY'      => 'required|numeric|min:0',
            'mapping.rowHeight'   => 'required|numeric|min:1',
            'mapping.maxRows'     => 'required|integer|min:1',
            'mapping.columns'     => 'required|array|min:1',
            'mapping.header'      => 'nullable|array',
            'mapping.date_format' => 'nullable|array',
            'mapping.time_format' => 'nullable|string',
        ], [
            'mapping.file.required'      => 'El mapeo debe tener un archivo PDF asociado.',
            'mapping.startY.required'    => 'El inicio Y de la tabla es obligatorio.',
            'mapping.rowHeight.required' => 'La altura de fila es obligatoria.',
            'mapping.maxRows.required'   => 'El número máximo de filas es obligatorio.',
            'mapping.columns.required'   => 'Debe haber al menos una columna mapeada.',
            'mapping.columns.min'        => 'Debe haber al menos una columna mapeada.',
        ]);

        $format->update([
            'mapping' => $request->mapping,
        ]);

        $this->updateConfigFile($format->slug, $request->mapping);

        session()->flash('success', 'Mapeo del formato guardado correctamente.');

        return response()->json(['success' => true]);
    }

    private function updateConfigFile(string $slug, array $mapping): void
    {
        $configPath = config_path('attendance_formats.php');
        $lockPath = $configPath . '.lock';

        $lock = fopen($lockPath, 'c');
        if (! flock($lock, LOCK_EX)) {
            fclose($lock);
            return;
        }

        try {
            $config = file_exists($configPath) ? include $configPath : [];
            $config[$slug] = $mapping;

            $content = "<?php\n\nreturn " . $this->arrayToPhp($config, 0) . ";\n";
            file_put_contents($configPath, $content, LOCK_EX);

            Artisan::call('config:clear');
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function arrayToPhp(array $array, int $depth = 0): string
    {
        $indent = str_repeat('    ', $depth + 1);
        $closingIndent = str_repeat('    ', $depth);
        $lines = [];

        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? '' : "'" . addslashes((string) $key) . "' => ";

            if (is_array($value)) {
                if ($this->isSmallArray($value)) {
                    $lines[] = $indent . $keyStr . $this->arrayToInlinePhp($value) . ',';
                } else {
                    $lines[] = $indent . $keyStr . $this->arrayToPhp($value, $depth + 1) . ',';
                }
            } elseif (is_string($value)) {
                $lines[] = $indent . $keyStr . "'" . addslashes($value) . "',";
            } elseif (is_bool($value)) {
                $lines[] = $indent . $keyStr . ($value ? 'true' : 'false') . ',';
            } elseif (is_null($value)) {
                $lines[] = $indent . $keyStr . 'null,';
            } elseif (is_float($value)) {
                $lines[] = $indent . $keyStr . $value . ',';
            } else {
                $lines[] = $indent . $keyStr . $value . ',';
            }
        }

        return "[\n" . implode("\n", $lines) . "\n" . $closingIndent . ']';
    }

    private function isSmallArray(array $array): bool
    {
        if (count($array) > 4) return false;
        foreach ($array as $value) {
            if (is_array($value)) return false;
        }
        return true;
    }

    private function arrayToInlinePhp(array $array): string
    {
        $items = [];
        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? '' : "'" . addslashes((string) $key) . "' => ";
            if (is_string($value)) {
                $items[] = $keyStr . "'" . addslashes($value) . "'";
            } elseif (is_bool($value)) {
                $items[] = $keyStr . ($value ? 'true' : 'false');
            } else {
                $items[] = $keyStr . $value;
            }
        }
        return '[' . implode(', ', $items) . ']';
    }

    private function removeFromConfigFile(string $slug): void
    {
        $configPath = config_path('attendance_formats.php');
        $lockPath = $configPath . '.lock';

        $lock = fopen($lockPath, 'c');
        if (! flock($lock, LOCK_EX)) {
            fclose($lock);
            return;
        }

        try {
            $config = file_exists($configPath) ? include $configPath : [];

            if (isset($config[$slug])) {
                unset($config[$slug]);

                $content = "<?php\n\nreturn " . $this->arrayToPhp($config, 0) . ";\n";
                file_put_contents($configPath, $content, LOCK_EX);

                Artisan::call('config:clear');
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
