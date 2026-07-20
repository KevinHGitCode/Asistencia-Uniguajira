<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Acumulado diario de impresiones/clics de un banner (ADR-0031), base del
 * reporte por rango de fechas para el patrocinador.
 */
class BannerDailyStat extends Model
{
    protected $fillable = ['banner_id', 'date', 'impressions', 'clicks'];

    protected $casts = ['date' => 'date'];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    /** Suma 1 a la columna dada ('impressions' o 'clicks') en la fila de hoy. */
    public static function bump(int $bannerId, string $column): void
    {
        // startOfDay para que búsqueda e inserción usen el mismo valor que
        // serializa el cast 'date' (Y-m-d 00:00:00).
        $stat = static::firstOrCreate([
            'banner_id' => $bannerId,
            'date' => now()->startOfDay(),
        ]);

        static::whereKey($stat->id)->increment($column);
    }
}
