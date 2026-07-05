<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Format extends Model
{
    protected $fillable = ['name', 'slug', 'file', 'mapping', 'mapping_outdated'];

    protected $casts = [
        'mapping' => 'array',
        'mapping_outdated' => 'boolean',
    ];

    public function dependencies()
    {
        return $this->belongsToMany(Dependency::class, 'dependency_format');
    }

    /**
     * Binario del PDF guardado en la BD (ADR-0017). La fuente durable del PDF:
     * sobrevive aunque se borre la carpeta `public/formats`.
     */
    public function fileRecord(): HasOne
    {
        return $this->hasOne(FormatFile::class);
    }

    /** ¿Hay una copia del PDF en la base de datos? */
    public function hasPdfInDb(): bool
    {
        return $this->fileRecord()->exists();
    }

    /** Bytes crudos del PDF desde la BD, o null si no hay copia en BD. */
    public function pdfContents(): ?string
    {
        $record = $this->fileRecord;

        return $record ? base64_decode($record->content) : null;
    }

    /** Guarda (o reemplaza) el binario del PDF en la BD. */
    public function storePdf(string $bytes, ?string $mime = 'application/pdf'): void
    {
        $this->fileRecord()->updateOrCreate(
            ['format_id' => $this->id],
            [
                'content' => base64_encode($bytes),
                'mime' => $mime,
                'size' => strlen($bytes),
                'hash' => hash('sha256', $bytes),
            ],
        );
    }

    /**
     * ¿El mapeo necesita atención? True si el PDF cambió tras el último mapeo
     * (mapping_outdated) o si hay un PDF cargado pero aún sin coordenadas.
     */
    public function needsMapping(): bool
    {
        return $this->mapping_outdated || ($this->file && empty($this->mapping));
    }
}
