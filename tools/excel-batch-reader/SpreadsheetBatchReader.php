<?php

namespace App\Support\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Motor de lectura por lotes de hojas de cálculo (CSV / XLSX / XLS).
 *
 * Agnóstico del dominio: no sabe de participantes, sedes ni de a qué tabla van
 * los datos. Solo se encarga de:
 *   1. Leer el archivo a filas (con fast-path nativo para CSV).
 *   2. Validar cabeceras / columnas requeridas.
 *   3. Iterar las filas de datos (saltando vacías) entregando cada fila como
 *      arreglo asociativo por nombre de columna.
 *
 * La lógica de qué hacer con cada fila (clasificar, insertar, etc.) la pone
 * quien lo usa, vía callback. No hay nada asíncrono ni de "staging" aquí.
 *
 * Dependencias:
 *   - PHP 8.1+ (por la firma de fgetcsv con $escape = '').
 *   - Para .xlsx/.xls:  composer require phpoffice/phpspreadsheet
 *     (los CSV no requieren ninguna dependencia externa).
 */
class SpreadsheetBatchReader
{
    /**
     * Lee TODAS las filas del archivo (incluida la cabecera) como arreglos
     * numéricos, equivalente a una matriz fila×columna.
     *
     * @return array<int, array<int, mixed>>
     */
    public function read(string $path, ?string $extension = null): array
    {
        $extension = strtolower($extension ?? pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($path);
        }

        return $this->readSpreadsheetRows($path);
    }

    /**
     * Lee el archivo, valida las columnas requeridas e itera cada fila de datos
     * (saltando las vacías), entregándola como arreglo asociativo por cabecera.
     *
     * @param  string                       $path             Ruta del archivo.
     * @param  array<int, string>           $requiredColumns  Cabeceras obligatorias.
     * @param  callable(array<string,mixed>,int):void $handle  Recibe (fila asociativa, número de fila Excel 1-based).
     * @param  string|null                  $extension        Forzar extensión (csv/xlsx/xls); si null se infiere del path.
     * @return int  Número de filas de datos procesadas (no vacías).
     *
     * @throws SpreadsheetReadException  Si el archivo está vacío o faltan columnas.
     */
    public function eachRow(string $path, array $requiredColumns, callable $handle, ?string $extension = null): int
    {
        $allRows = $this->read($path, $extension);

        if (empty($allRows)) {
            throw new SpreadsheetReadException('El archivo está vacío.');
        }

        $headers = $this->normalizeHeaders($allRows[0]);
        $colIndex = $this->columnIndex($headers);

        $this->assertRequiredColumns($colIndex, $requiredColumns);

        // Quita la fila de cabecera; las de datos empiezan en la fila 2 de Excel.
        array_shift($allRows);

        $processed = 0;
        $rowNumber = 1; // 1 = cabecera; los datos arrancan en 2

        foreach ($allRows as $raw) {
            $rowNumber++;
            $values = array_values((array) $raw);

            // Saltar filas completamente vacías.
            if (empty(array_filter($values, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $assoc = [];
            foreach ($colIndex as $name => $pos) {
                $assoc[$name] = $values[$pos] ?? null;
            }

            $handle($assoc, $rowNumber);
            $processed++;
        }

        return $processed;
    }

    /**
     * Lee el archivo y devuelve TODAS las filas de datos como arreglos
     * asociativos por cabecera (útil cuando se prefiere recolectar en vez de
     * usar callback). Misma validación que eachRow.
     *
     * @param  array<int, string> $requiredColumns
     * @return array<int, array<string, mixed>>
     *
     * @throws SpreadsheetReadException
     */
    public function rows(string $path, array $requiredColumns = [], ?string $extension = null): array
    {
        $rows = [];
        $this->eachRow($path, $requiredColumns, function (array $row) use (&$rows) {
            $rows[] = $row;
        }, $extension);

        return $rows;
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Cabeceras
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @param  array<int, mixed> $headerRow
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        return array_map(fn ($h) => trim((string) ($h ?? '')), array_values($headerRow));
    }

    /**
     * Mapa  nombre-de-columna => índice  (ignora columnas con cabecera vacía).
     *
     * @param  array<int, string> $headers
     * @return array<string, int>
     */
    private function columnIndex(array $headers): array
    {
        $index = [];
        foreach ($headers as $pos => $name) {
            if ($name !== '') {
                $index[$name] = $pos;
            }
        }

        return $index;
    }

    /**
     * @param  array<string, int> $colIndex
     * @param  array<int, string> $required
     *
     * @throws SpreadsheetReadException
     */
    private function assertRequiredColumns(array $colIndex, array $required): void
    {
        $missing = array_values(array_filter(
            $required,
            fn ($col) => ! isset($colIndex[$col])
        ));

        if (! empty($missing)) {
            throw new SpreadsheetReadException(
                'El archivo no tiene las siguientes columnas requeridas: '
                .implode(', ', array_map(fn ($c) => "«{$c}»", $missing))
                .'. Descarga la plantilla oficial y vuelve a intentarlo.'
            );
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Lectura XLSX / XLS  (PhpSpreadsheet)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readSpreadsheetRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        // Solo valores (sin estilos/fórmulas) → mucho más rápido y liviano.
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows ?: [];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Lectura CSV  (fast-path nativo, sin dependencias)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Lee un CSV de forma nativa (mucho más rápido que un lector de Excel):
     *   - Quita BOM UTF-8.
     *   - Normaliza la codificación a UTF-8 (Windows-1252 es común en Excel/Windows).
     *   - Detecta el separador (',' / ';' / tabulador).
     *   - Respeta comillas y saltos de línea dentro de campos.
     *
     * @return array<int, array<int, string>>
     */
    private function readCsvRows(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || $content === '') {
            return [];
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $delimiter = $this->detectCsvDelimiter($content);

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $rows[] = $data;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Detecta el separador del CSV mirando la primera línea.
     */
    private function detectCsvDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: '';

        $counts = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Utilidad de limpieza de texto (opcional)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Normaliza un valor de celda: recorta, colapsa espacios y repara mojibake
     * frecuente de archivos exportados con codificación mixta. Úsala sobre los
     * campos de texto si lo necesitas; el motor no la aplica por sí solo.
     */
    public static function normalizeText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = strtr($text, [
            'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
            'Ã' => 'Á', 'Ã‰' => 'É', 'Ã' => 'Í', 'Ã“' => 'Ó', 'Ãš' => 'Ú',
            'Ã±' => 'ñ', 'Ã‘' => 'Ñ', 'Ã¼' => 'ü', 'Ãœ' => 'Ü', 'Â' => '',
        ]);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }
}
