<?php

namespace App\Support\Import;

use RuntimeException;

/**
 * Error "esperable" al leer una hoja de cálculo (archivo vacío, columnas
 * requeridas faltantes, etc.) cuyo mensaje es legible para el usuario final.
 *
 * Captúralo en el controlador para mostrarlo como error de formulario:
 *
 *   try {
 *       $reader->eachRow($path, ['Documento', 'Correo'], fn ($row) => ...);
 *   } catch (SpreadsheetReadException $e) {
 *       return back()->withErrors(['archivo' => $e->getMessage()]);
 *   }
 */
class SpreadsheetReadException extends RuntimeException {}
