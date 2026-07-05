<?php

namespace App\Console\Commands;

use App\Models\Format;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Respalda en la BD los PDF de formato que hoy solo viven en disco (ADR-0017).
 * Idempotente: salta los formatos que ya tienen copia en BD o cuyo archivo no
 * está en disco. Ejecutar una vez tras desplegar el cambio:
 *
 *   php artisan formats:pdf-a-bd
 */
class BackfillFormatPdfs extends Command
{
    protected $signature = 'formats:pdf-a-bd';

    protected $description = 'Copia a la base de datos los PDF de formato que hoy solo están en disco';

    public function handle(): int
    {
        $copiados = 0;
        $saltados = 0;

        foreach (Format::whereNotNull('file')->get() as $format) {
            if ($format->hasPdfInDb()) {
                $saltados++;

                continue;
            }

            if (! Storage::disk('formats')->exists($format->file)) {
                $this->warn("Sin archivo en disco: #{$format->id} {$format->slug} ({$format->file})");
                $saltados++;

                continue;
            }

            $format->storePdf(Storage::disk('formats')->get($format->file));
            $copiados++;
        }

        $this->info("PDFs copiados a la BD: {$copiados}. Saltados: {$saltados}.");

        return self::SUCCESS;
    }
}
