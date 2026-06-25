<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Presencia y métricas de uso por usuario (ADR-0010, frentes 2 y 3).
 *
 * - Presencia "en línea": derivada de la tabla `sessions` (SESSION_DRIVER=database),
 *   usando `last_activity` reciente. No requiere websockets ni infraestructura extra.
 * - Uso: derivado de `activity_logs` (pares login/logout del módulo `sesion` y
 *   acciones por módulo).
 */
class UserActivityService
{
    /** Ventana (segundos) para considerar a un usuario "en línea". */
    public const ONLINE_THRESHOLD_SECONDS = 300; // 5 minutos

    /** Tope para descartar pares login/logout corruptos al sumar tiempo de uso. */
    private const MAX_SESSION_SECONDS = 12 * 3600;

    /**
     * IDs de usuarios con sesión activa en los últimos ONLINE_THRESHOLD_SECONDS.
     *
     * @return array<int, int>
     */
    public function onlineUserIds(): array
    {
        $since = Carbon::now()->getTimestamp() - self::ONLINE_THRESHOLD_SECONDS;

        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $since)
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Número de usuarios en línea ahora mismo. */
    public function onlineCount(): int
    {
        return count($this->onlineUserIds());
    }

    public function isOnline(int $userId): bool
    {
        return in_array($userId, $this->onlineUserIds(), true);
    }

    /** Última actividad registrada en `sessions` para el usuario (o null). */
    public function lastSeenAt(int $userId): ?Carbon
    {
        $timestamp = DB::table('sessions')->where('user_id', $userId)->max('last_activity');

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * Métricas de uso de un usuario para la vista de detalle.
     *
     * @return array{is_online: bool, last_seen: ?Carbon, login_count: int,
     *               last_login: ?Carbon, usage_seconds: int,
     *               actions_by_module: array<int, array{module: string, count: int}>}
     */
    public function usageFor(User $user): array
    {
        $sessionLogs = ActivityLog::query()
            ->where('user_id', $user->id)
            ->where('module', 'sesion')
            ->orderBy('created_at')
            ->get(['action', 'created_at']);

        $loginCount = $sessionLogs->where('action', 'login')->count();
        $lastLogin  = $sessionLogs->where('action', 'login')->last()?->created_at;

        $actionsByModule = ActivityLog::query()
            ->where('user_id', $user->id)
            ->select('module', DB::raw('COUNT(*) as count'))
            ->groupBy('module')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['module' => $row->module, 'count' => (int) $row->count])
            ->all();

        return [
            'is_online'         => $this->isOnline($user->id),
            'last_seen'         => $this->lastSeenAt($user->id),
            'login_count'       => $loginCount,
            'last_login'        => $lastLogin,
            'usage_seconds'     => $this->usageSecondsFromLogs($sessionLogs),
            'actions_by_module' => $actionsByModule,
        ];
    }

    /**
     * Tiempo total aproximado en la app, sumando pares login→logout consecutivos.
     * Es aproximado: los cierres sin logout (cerrar pestaña) no se contabilizan.
     *
     * @param  \Illuminate\Support\Collection<int, ActivityLog>  $sessionLogs
     */
    private function usageSecondsFromLogs($sessionLogs): int
    {
        $total = 0;
        $loginAt = null;

        foreach ($sessionLogs as $log) {
            if ($log->action === 'login') {
                $loginAt = $log->created_at;
            } elseif ($log->action === 'logout' && $loginAt) {
                $diff = $log->created_at->getTimestamp() - $loginAt->getTimestamp();
                if ($diff > 0 && $diff < self::MAX_SESSION_SECONDS) {
                    $total += $diff;
                }
                $loginAt = null;
            }
        }

        return $total;
    }
}
