<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error "esperable" del parseo de una importación (archivo vacío, columnas
 * faltantes, etc.) cuyo mensaje es legible para el usuario.
 *
 * - En el flujo inline el controlador lo captura y lo muestra como error de
 *   formulario (`back()->withErrors`).
 * - En el flujo encolado el job lo captura y marca el lote como `error` con este
 *   mensaje, sin tratarlo como una falla dura del job.
 */
class ImportParseException extends RuntimeException {}
