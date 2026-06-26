<?php

namespace App\Notifications;

use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Avisa al usuario que subió una importación que su lote ya terminó de procesarse
 * y está listo para revisar (ADR-0018, primer consumidor de ADR-0004).
 */
class ImportBatchReady extends Notification
{
    use Queueable;

    public function __construct(public ImportBatch $batch) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $resumen = trim(sprintf(
            '%d nuevos · %d actualizan · %d omitidos',
            $this->batch->new_count,
            $this->batch->update_count,
            $this->batch->skipped_count,
        ));

        return [
            'tipo' => 'import.listo',
            'titulo' => 'Importación lista para revisar',
            'mensaje' => "El archivo «{$this->batch->original_filename}» terminó de procesarse: {$resumen}.",
            'url' => route('participants-import.review', $this->batch),
            'icono' => 'clipboard-document-check',
        ];
    }
}
