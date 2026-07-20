<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Imagen de un banner de anuncio guardada en la base de datos (ADR-0030),
 * en base64 (`content`) — mismo patrón que FormatFile (ADR-0017). Usar
 * `Banner::imageContents()` para leer los bytes.
 */
class BannerFile extends Model
{
    protected $fillable = ['banner_id', 'content', 'mime', 'size', 'hash'];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }
}
