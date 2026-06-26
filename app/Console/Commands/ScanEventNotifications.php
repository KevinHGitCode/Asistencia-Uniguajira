<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventEndingSoon;
use App\Notifications\EventStartingSoon;
use Illuminate\Console\Command;

/**
 * Escanea los eventos y crea avisos in-app de "próximo" y "por finalizar"
 * (ADR-0018). Idempotente: usa `reminder_notified_at` / `ending_notified_at`
 * para no reenviar el mismo aviso en cada tick del cron.
 */
class ScanEventNotifications extends Command
{
    protected $signature = 'notifications:escanear-eventos';

    protected $description = 'Crea avisos in-app de eventos próximos y por finalizar';

    public function handle(): int
    {
        $now = now();
        $startWindow = (int) config('notifications.event_starting_window', 60);
        $endWindow = (int) config('notifications.event_ending_window', 30);

        // Candidatos: eventos no terminados de hoy y mañana (cubre ventanas que
        // crucen la medianoche). El cálculo fino de fecha+hora se hace en PHP
        // para ser portable entre SQLite (local) y MySQL (Hostinger).
        $events = Event::query()
            ->whereNull('ended_at')
            ->whereNotNull('user_id')
            ->whereDate('date', '>=', $now->toDateString())
            ->whereDate('date', '<=', $now->copy()->addDay()->toDateString())
            ->with('user')
            ->get();

        $startCount = 0;
        $endCount = 0;

        foreach ($events as $event) {
            $start = $event->startsAt();
            if ($start !== null
                && $event->reminder_notified_at === null
                && $start->betweenIncluded($now, $now->copy()->addMinutes($startWindow))
            ) {
                $event->user?->notify(new EventStartingSoon($event));
                $event->forceFill(['reminder_notified_at' => $now])->save();
                $startCount++;
            }

            $end = $event->endsAt();
            if ($end !== null
                && $event->ending_notified_at === null
                && $end->betweenIncluded($now, $now->copy()->addMinutes($endWindow))
            ) {
                $event->user?->notify(new EventEndingSoon($event));
                $event->forceFill(['ending_notified_at' => $now])->save();
                $endCount++;
            }
        }

        $this->info("Avisos creados: {$startCount} próximos, {$endCount} por finalizar.");

        return self::SUCCESS;
    }
}
