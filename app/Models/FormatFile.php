<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Binario del PDF de un formato guardado en la base de datos (ADR-0017), para
 * que sobreviva aunque se borre la carpeta `public/formats`. El contenido se
 * guarda en base64 (`content`); usar `Format::pdfContents()` para leer los bytes.
 */
class FormatFile extends Model
{
    protected $fillable = ['format_id', 'content', 'mime', 'size', 'hash'];

    public function format(): BelongsTo
    {
        return $this->belongsTo(Format::class);
    }
}
