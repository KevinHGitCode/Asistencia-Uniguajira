<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Purga los lotes de importación ya procesados (aprobado / rechazado / error)
 * más antiguos que la retención configurada (ADR-0004), junto con sus filas en
 * staging (cascade) y cualquier archivo huérfano en `imports/`.
 */
class PruneImportBatches extends Command
{
    protected $signature = 'imports:limpiar';

    protected $description = 'Borra lotes de importación procesados más antiguos que la retención y archivos huérfanos';

    public function handle(): int
    {
        $days = (int) config('notifications.import_retention_days', 30);
        $cutoff = now()->subDays($days);

        // Los staged_participants se borran en cascada (FK cascadeOnDelete).
        $deleted = ImportBatch::query()
            ->whereIn('status', ['aprobado', 'rechazado', 'error'])
            ->where('created_at', '<', $cutoff)
            ->delete();

        // Barrido de archivos huérfanos en imports/ (de cargas interrumpidas).
        $sweptFiles = 0;
        $disk = Storage::disk('local');
        foreach ($disk->files('imports') as $file) {
            if ($disk->lastModified($file) < $cutoff->getTimestamp()) {
                $disk->delete($file);
                $sweptFiles++;
            }
        }

        $this->info("Lotes purgados: {$deleted}. Archivos huérfanos borrados: {$sweptFiles}.");

        return self::SUCCESS;
    }
}
