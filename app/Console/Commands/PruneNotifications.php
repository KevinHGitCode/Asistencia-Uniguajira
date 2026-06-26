<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Purga las notificaciones ya leídas más antiguas que la ventana de retención
 * (ADR-0018), para que la tabla `notifications` no crezca sin techo.
 */
class PruneNotifications extends Command
{
    protected $signature = 'notifications:limpiar';

    protected $description = 'Borra notificaciones leídas más antiguas que la retención configurada';

    public function handle(): int
    {
        $days = (int) config('notifications.read_retention_days', 30);
        $cutoff = now()->subDays($days);

        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        $this->info("Notificaciones leídas purgadas: {$deleted} (anteriores a {$cutoff->toDateString()}).");

        return self::SUCCESS;
    }
}
