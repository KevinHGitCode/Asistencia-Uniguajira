<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Banner de anuncio/patrocinio mostrado en la página pública de registro de
 * asistencia (ADR-0030). La imagen vive en la BD (banner_files), siguiendo el
 * mismo patrón que los PDF de formato (ADR-0017).
 */
class Banner extends Model
{
    protected $fillable = ['name', 'target_url', 'starts_at', 'ends_at', 'active'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'active' => 'boolean',
    ];

    public function fileRecord(): HasOne
    {
        return $this->hasOne(BannerFile::class);
    }

    /** Banners activos y dentro de su ventana de vigencia (fechas opcionales). */
    public function scopeVigentes(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    /** ¿Está vigente hoy? (para pintar el estado en el listado admin) */
    public function isVigente(): bool
    {
        return $this->active
            && (! $this->starts_at || $this->starts_at->isPast() || $this->starts_at->isToday())
            && (! $this->ends_at || $this->ends_at->isFuture() || $this->ends_at->isToday());
    }

    /** Bytes crudos de la imagen desde la BD, o null si no hay. */
    public function imageContents(): ?string
    {
        $record = $this->fileRecord;

        return $record ? base64_decode($record->content) : null;
    }

    /** Guarda (o reemplaza) la imagen del banner en la BD. */
    public function storeImage(string $bytes, ?string $mime): void
    {
        $this->fileRecord()->updateOrCreate(
            ['banner_id' => $this->id],
            [
                'content' => base64_encode($bytes),
                'mime' => $mime,
                'size' => strlen($bytes),
                'hash' => hash('sha256', $bytes),
            ],
        );
    }
}
