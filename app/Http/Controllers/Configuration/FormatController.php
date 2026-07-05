<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Dependency;
use App\Models\Format;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormatController extends Controller
{
    /**
     * Verifica que FPDI pueda abrir el PDF.
     * Devuelve null si es compatible, o un mensaje de error si no lo es.
     */
    private function validatePdfCompatibility(string $filePath): ?string
    {
        try {
            $fpdi = new \setasign\Fpdi\Tfpdf\Fpdi;
            $fpdi->setSourceFile($filePath);

            return null;
        } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            return 'El archivo PDF usa un tipo de compresión no soportado. '
                .'Por favor, abra el PDF en LibreOffice Draw (Archivo → Exportar como PDF) '
                .'o use una herramienta online como smallpdf.com (opción "PDF a PDF/A") para re-guardarlo. '
                .'También puedes intentar abrirlo y re-guardarlo desde el navegador (arrastrando el PDF a una pestaña nueva y luego usando "Imprimir" → "Guardar como PDF").';
        } catch (\Exception $e) {
            return 'No se pudo procesar el archivo PDF: '.$e->getMessage();
        }
    }

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
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:formats,slug'],
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique' => 'Ya existe un formato con ese identificador.',
            'slug.regex' => 'El identificador solo puede contener letras, números, guiones y guiones bajos.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max' => 'El archivo no debe superar los 5MB.',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('pdf_file')) {
            $fileName = $request->slug.'_'.time().'.pdf';
            $request->file('pdf_file')->storeAs('', $fileName, 'formats');

            $storedPath = Storage::disk('formats')->path($fileName);
            $pdfError = $this->validatePdfCompatibility($storedPath);
            if ($pdfError) {
                Storage::disk('formats')->delete($fileName);

                return back()->withInput()->withErrors(['pdf_file' => $pdfError]);
            }
            $data['file'] = $fileName;
        }

        $format = Format::create($data);

        // Guarda una copia del PDF en la BD (ADR-0017): fuente durable que
        // sobrevive aunque se borre la carpeta public/formats. El disco queda
        // como caché/respaldo secundario.
        if ($request->hasFile('pdf_file') && isset($data['file'])) {
            $format->storePdf(Storage::disk('formats')->get($data['file']));
        }

        if ($request->has('dependencies')) {
            $format->dependencies()->sync($request->dependencies);
        }

        ActivityLogService::log('crear', 'formatos', "Creó el formato '{$format->name}'", $format);

        return redirect()->route('formats.index')->with('success', 'Formato creado correctamente.');
    }

    public function update(Request $request, Format $format)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:formats,slug,'.$format->id],
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:dependencies,id',
        ], [
            'name.required' => 'El nombre del formato es obligatorio.',
            'slug.required' => 'El identificador es obligatorio.',
            'slug.unique' => 'Ya existe un formato con ese identificador.',
            'slug.regex' => 'El identificador solo puede contener letras, números, guiones y guiones bajos.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max' => 'El archivo no debe superar los 5MB.',
        ]);

        $data = $request->only('name', 'slug');
        $mappingBecameOutdated = false;

        if ($request->hasFile('pdf_file')) {
            $newFileName = $request->slug.'_'.time().'.pdf';
            $request->file('pdf_file')->storeAs('', $newFileName, 'formats');

            $storedPath = Storage::disk('formats')->path($newFileName);
            $pdfError = $this->validatePdfCompatibility($storedPath);
            if ($pdfError) {
                Storage::disk('formats')->delete($newFileName);

                return back()->withInput()->withErrors(['pdf_file' => $pdfError]);
            }

            if ($format->file && Storage::disk('formats')->exists($format->file)) {
                Storage::disk('formats')->delete($format->file);
            }

            $data['file'] = $newFileName;

            // El PDF cambió: si ya había un mapeo, sus coordenadas quedan
            // desactualizadas respecto al nuevo archivo (ADR-0015). Se marca
            // para avisar al usuario que debe re-mapear.
            if (! empty($format->mapping)) {
                $data['mapping_outdated'] = true;
                $mappingBecameOutdated = true;
            }
        }

        $original = $format->only(['name', 'slug', 'file']);
        $originalDeps = $format->dependencies->pluck('id')->sort()->values()->toArray();

        $format->update($data);
        $format->dependencies()->sync($request->dependencies ?? []);

        // Actualiza la copia del PDF en la BD cuando se sube uno nuevo (ADR-0017).
        if ($request->hasFile('pdf_file') && isset($data['file'])) {
            $format->storePdf(Storage::disk('formats')->get($data['file']));
        }

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $format->$field;
            if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
                $changes[$field] = ['old' => $oldValue ?? '—', 'new' => $newValue ?? '—'];
            }
        }
        $newDeps = collect($request->dependencies ?? [])->map(fn ($id) => (int) $id)->sort()->values()->toArray();
        if ($originalDeps !== $newDeps) {
            $changes['dependencias'] = ['old' => implode(', ', $originalDeps), 'new' => implode(', ', $newDeps)];
        }

        ActivityLogService::log('editar', 'formatos', "Editó el formato '{$format->name}'", $format, $changes);

        if ($mappingBecameOutdated) {
            return redirect()->route('formats.index')->with('success',
                'Formato actualizado. Cambiaste el PDF, así que el mapeo de coordenadas quedó marcado como pendiente: entra a «Mapear coordenadas» y vuelve a guardarlo para que la tabla salga cuadrada.');
        }

        return redirect()->route('formats.index')->with('success', 'Formato actualizado correctamente.');
    }

    public function destroy(Format $format)
    {
        if ($format->file && Storage::disk('formats')->exists($format->file)) {
            Storage::disk('formats')->delete($format->file);
        }

        $name = $format->name;
        $format->dependencies()->detach();
        $format->delete();

        ActivityLogService::log('eliminar', 'formatos', "Eliminó el formato '{$name}'");

        return redirect()->route('formats.index')->with('success', 'Formato eliminado correctamente.');
    }

    public function mapper(Format $format)
    {
        $existingMapping = $format->mapping ?? config("attendance_formats.{$format->slug}") ?? null;

        return view('administration.formats.mapper', compact('format', 'existingMapping'));
    }

    public function saveMapping(Request $request, Format $format)
    {
        $request->validate([
            'mapping' => 'required|array',
            'mapping.file' => 'required|string',
            'mapping.startY' => 'required|numeric|min:0',
            'mapping.rowHeight' => 'required|numeric|min:1',
            'mapping.maxRows' => 'required|integer|min:1',
            'mapping.columns' => 'required|array|min:1',
            'mapping.header' => 'nullable|array',
            'mapping.date_format' => 'nullable|array',
            'mapping.time_format' => 'nullable|string',
        ], [
            'mapping.file.required' => 'El mapeo debe tener un archivo PDF asociado.',
            'mapping.startY.required' => 'El inicio Y de la tabla es obligatorio.',
            'mapping.rowHeight.required' => 'La altura de fila es obligatoria.',
            'mapping.maxRows.required' => 'El número máximo de filas es obligatorio.',
            'mapping.columns.required' => 'Debe haber al menos una columna mapeada.',
            'mapping.columns.min' => 'Debe haber al menos una columna mapeada.',
        ]);

        // ADR-0015: la BD es la única fuente de verdad del mapeo. Ya no se
        // escribe el espejo en config/attendance_formats.php en tiempo de
        // ejecución (frágil en hosting compartido e incompatible con
        // config:cache). Guardar el mapeo lo deja al día → ya no está pendiente.
        $format->update([
            'mapping' => $request->mapping,
            'mapping_outdated' => false,
        ]);

        session()->flash('success', 'Mapeo del formato guardado correctamente.');

        return response()->json(['success' => true]);
    }
}
