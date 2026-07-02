<?php

namespace App\Jobs;

use App\Exceptions\ImportParseException;
use App\Models\ImportBatch;
use App\Notifications\ImportBatchReady;
use App\Services\ParticipantImportParser;
use App\Support\ImportContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Parsea un archivo de importación de participantes en segundo plano (ADR-0004).
 *
 * Se usa para archivos `.xlsx` grandes, que bloquearían el request. En Hostinger
 * (hosting compartido) lo procesa el cron vía `queue:work --stop-when-empty`.
 * Al terminar, avisa al usuario con una notificación in-app (ADR-0018).
 */
class ParseParticipantImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Un solo intento: si el archivo está mal, reintentar no ayuda. */
    public int $tries = 1;

    /** Margen amplio para listas grandes. */
    public int $timeout = 1800;

    public function __construct(
        public ImportBatch $batch,
        public string $disk,
        public string $relativePath,
        public string $extension,
        public ImportContext $context,
    ) {}

    public function handle(ParticipantImportParser $parser): void
    {
        $absolutePath = Storage::disk($this->disk)->path($this->relativePath);

        try {
            $parser->parse($this->batch, $absolutePath, $this->extension, $this->context);

            // Aviso in-app: el lote ya está listo para revisar (ADR-0018).
            $this->notifyUser();
        } catch (ImportParseException $e) {
            // Error esperado (archivo vacío / columnas faltantes): no es una falla
            // dura del job; se marca el lote para que el usuario lo vea.
            $this->markFailed($e->getMessage());
        } finally {
            $this->cleanupFile();
        }
    }

    /**
     * Falla dura (excepción inesperada): Laravel llama a este método.
     */
    public function failed(?Throwable $e): void
    {
        Log::error('ParseParticipantImportJob falló: '.($e?->getMessage() ?? 'desconocido'));

        $this->markFailed('Ocurrió un error inesperado al procesar el archivo. Inténtalo de nuevo o sube el archivo en formato CSV.');
        $this->cleanupFile();
    }

    private function markFailed(string $message): void
    {
        $batch = $this->batch->fresh();

        if ($batch && $batch->isProcessing()) {
            $batch->update([
                'status' => 'error',
                'error_message' => $message,
            ]);
        }
    }

    private function cleanupFile(): void
    {
        try {
            Storage::disk($this->disk)->delete($this->relativePath);
        } catch (Throwable $e) {
            // El barrido de retención lo recogerá después; no interrumpimos.
        }
    }

    private function notifyUser(): void
    {
        try {
            if (! Schema::hasTable('notifications')) {
                return;
            }

            $this->batch->user?->notify(new ImportBatchReady($this->batch->fresh()));
        } catch (Throwable $e) {
            Log::warning('No se pudo notificar que el lote de participantes esta listo: '.$e->getMessage());
        }
    }
}
