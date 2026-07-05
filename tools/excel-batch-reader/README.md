# Excel/CSV Batch Reader (portable)

Motor de lectura por lotes de hojas de cálculo, extraído del importador de
participantes de *Asistencia Uniguajira* (`ParticipantImportParser`), **sin** la
parte asíncrona/cola, **sin** la pasarela de revisión (staging) y **sin** la
lógica de dominio (participantes, sedes, etc.).

Es solo el **núcleo de lectura**: archivo → filas → validación de cabeceras →
iteración de filas. Qué hacer con cada fila lo decides tú.

## Qué incluye

- **`SpreadsheetBatchReader`** — el motor:
  - Lee **CSV** con un *fast-path* nativo (≈8× más rápido que un lector de Excel):
    quita BOM, normaliza codificación (Windows-1252 → UTF-8) y detecta el
    separador (`,` `;` o tab).
  - Lee **XLSX/XLS** con PhpSpreadsheet en modo *solo datos* (rápido y liviano).
  - Valida **columnas requeridas** y entrega cada fila como arreglo **asociativo
    por nombre de columna**.
  - Salta filas completamente vacías.
  - `normalizeText()` opcional para limpiar texto (recorta, colapsa espacios,
    repara *mojibake*).
- **`SpreadsheetReadException`** — error legible para el usuario (archivo vacío,
  columnas faltantes) que puedes mostrar en el formulario.

## Qué NO incluye (a propósito)

- Nada asíncrono (jobs / colas / cron).
- Pasarela de revisión / tablas de *staging*.
- Inserción a base de datos (ver el snippet de abajo si la necesitas).
- Validación de la subida HTTP (tamaño, mimes): eso va en tu controlador.

## Dependencias

- **PHP 8.1+** (por la firma de `fgetcsv` con `$escape = ''`).
- Solo si vas a leer **.xlsx / .xls**:
  ```bash
  composer require phpoffice/phpspreadsheet
  ```
  Para **solo CSV** no necesitas ninguna dependencia externa.

## Instalación

1. Copia `SpreadsheetBatchReader.php` y `SpreadsheetReadException.php` a tu
   proyecto, p. ej. en `app/Support/Import/`.
2. Ajusta el `namespace` (vienen como `App\Support\Import`) al de tu proyecto.

## Uso

### 1) Iterar fila por fila (recomendado para archivos grandes)

```php
use App\Support\Import\SpreadsheetBatchReader;
use App\Support\Import\SpreadsheetReadException;

$reader = new SpreadsheetBatchReader();

$total = $reader->eachRow(
    path: $rutaAbsoluta,                 // p. ej. storage_path('app/cargas/x.xlsx')
    requiredColumns: ['Documento', 'Nombres', 'Correo'],
    handle: function (array $row, int $rowNumber) {
        // $row es asociativo por cabecera: $row['Documento'], $row['Correo'], ...
        $documento = trim((string) ($row['Documento'] ?? ''));
        // ...tu lógica: validar, clasificar, acumular, etc.
    },
    // extension: 'csv'  // opcional; si se omite se infiere del path
);
```

### 2) Recolectar todas las filas

```php
$rows = $reader->rows($rutaAbsoluta, ['Documento', 'Correo']);
// $rows = [ ['Documento' => '123', 'Correo' => 'a@b.co', ...], ... ]
```

### 3) En un controlador Laravel (subida)

```php
public function import(Request $request, SpreadsheetBatchReader $reader)
{
    $request->validate([
        'archivo' => 'required|file|mimes:xlsx,xls,csv|max:20480',
    ]);

    $uploaded = $request->file('archivo');
    $path = $uploaded->getRealPath();
    $ext  = strtolower($uploaded->getClientOriginalExtension());

    try {
        $reader->eachRow($path, ['Documento', 'Correo'], function (array $row) {
            // ...
        }, $ext);
    } catch (SpreadsheetReadException $e) {
        return back()->withErrors(['archivo' => $e->getMessage()]);
    }

    return back()->with('success', 'Archivo procesado.');
}
```

## Añadir inserción por lotes (opcional)

El motor no inserta; si quieres el patrón eficiente (buffer + una sola
transacción, como en el importador original), envuelve tu callback:

```php
use Illuminate\Support\Facades\DB;

$buffer = [];
$flush = function () use (&$buffer) {
    if ($buffer) { DB::table('mi_tabla')->insert($buffer); $buffer = []; }
};

DB::transaction(function () use ($reader, $path, &$buffer, $flush) {
    $reader->eachRow($path, ['Documento', 'Correo'], function (array $row) use (&$buffer, $flush) {
        $buffer[] = [
            'document' => trim((string) ($row['Documento'] ?? '')),
            'email'    => $row['Correo'] ?? null,
            // ...
        ];
        if (count($buffer) >= 500) { $flush(); }   // inserta de a 500
    });
    $flush(); // resto
});
```

> En SQLite, envolver todo en **una** transacción evita un `fsync` por fila y
> acelera mucho. En MySQL también reduce overhead por sentencia.

## Origen

Extraído de `app/Services/ParticipantImportParser.php` (proyecto Asistencia
Uniguajira). La versión original añade, sobre este motor: clasificación de filas,
resolución de catálogos por sede, *staging* para revisión previa y procesamiento
asíncrono por cola — todo eso queda **fuera** de este paquete portable.
