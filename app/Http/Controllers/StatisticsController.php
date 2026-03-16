<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Services\StatisticsService;
use App\Traits\AppliesStatisticsFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    use AppliesStatisticsFilters;

    // ── Endpoints de resumen (1 request = todos los datos del módulo) ────────

    /**
     * Todos los datos para el módulo "Por Asistencias".
     */
    public function asistenciasSummary(Request $request): JsonResponse
    {
        $filters = $this->scopeToUser($this->getFilters($request), Auth::user());

        if ($filters === null) {
            return response()->json([
                'counters' => ['events' => 0, 'attendances' => 0, 'participants' => 0],
                'charts'   => [
                    'attendancesByProgram' => [],
                    'topEvents'            => [],
                    'topParticipants'      => [],
                    'byRole'               => [],
                    'bySex'                => [],
                    'byGroup'              => [],
                ],
            ]);
        }

        $s = new StatisticsService($filters);

        return response()->json([
            'counters' => [
                'events'       => $s->totalEvents(),
                'attendances'  => $s->totalAttendances(),
                'participants' => $s->totalParticipants(),
            ],
            'charts' => [
                'attendancesByProgram' => $s->attendancesByProgram(),
                'topEvents'            => $s->topEvents(),
                'topParticipants'      => $s->topParticipants(),
                'byRole'               => $s->attendancesByType(),
                'bySex'                => $s->attendancesByDetailField('gender'),
                'byGroup'              => $s->attendancesByDetailField('priority_group'),
            ],
        ]);
    }

    /**
     * Todos los datos para el módulo "Por Participantes".
     */
    public function participantesSummary(Request $request): JsonResponse
    {
        $filters = $this->scopeToUser($this->getFilters($request), Auth::user());

        if ($filters === null) {
            return response()->json([
                'counters' => ['events' => 0, 'participants' => 0],
                'charts'   => [
                    'participantsByProgram' => [],
                    'byRole'               => [],
                    'bySex'                => [],
                    'byGroup'              => [],
                ],
            ]);
        }

        $s = new StatisticsService($filters);

        return response()->json([
            'counters' => [
                'events'       => $s->totalEvents(),
                'participants' => $s->totalParticipants(),
            ],
            'charts' => [
                'participantsByProgram' => $s->participantsByProgram(),
                'byRole'                => $s->participantsByType(),
                'bySex'                 => $s->participantsByDetailField('gender'),
                'byGroup'               => $s->participantsByDetailField('priority_group'),
            ],
        ]);
    }

    // ── Endpoints individuales (compatibilidad con rutas existentes) ─────────

    public function totalEvents(Request $request)
    {
        return $this->svc($request)->totalEvents();
    }

    public function totalAttendances(Request $request)
    {
        return $this->svc($request)->totalAttendances();
    }

    public function totalParticipants(Request $request)
    {
        return $this->svc($request)->totalParticipants();
    }

    public function attendancesByProgram(Request $request)
    {
        return $this->svc($request)->attendancesByProgram();
    }

    public function participantsByProgram(Request $request)
    {
        return $this->svc($request)->participantsByProgram();
    }

    public function eventsOverTime(Request $request)
    {
        return $this->svc($request)->eventsOverTime();
    }

    public function attendancesOverTime(Request $request)
    {
        return $this->svc($request)->attendancesOverTime();
    }

    public function topEvents(Request $request)
    {
        return $this->svc($request)->topEvents();
    }

    public function topParticipants(Request $request)
    {
        return $this->svc($request)->topParticipants();
    }

    public function topUsers(Request $request)
    {
        return $this->svc($request)->topUsers();
    }

    public function eventsByRole(Request $request)
    {
        return $this->svc($request)->eventsByUserRole();
    }

    public function eventsByUser(Request $request)
    {
        return $this->svc($request)->eventsByUser();
    }

    public function attendancesByRole(Request $request)
    {
        return $this->svc($request)->attendancesByType();
    }

    public function attendancesBySex(Request $request)
    {
        return $this->svc($request)->attendancesByDetailField('gender');
    }

    public function attendancesByGroup(Request $request)
    {
        return $this->svc($request)->attendancesByDetailField('priority_group');
    }

    public function participantsByRole(Request $request)
    {
        return $this->svc($request)->participantsByType();
    }

    public function participantsBySex(Request $request)
    {
        return $this->svc($request)->participantsByDetailField('gender');
    }

    public function participantsByGroup(Request $request)
    {
        return $this->svc($request)->participantsByDetailField('priority_group');
    }

    // ── Helpers privados ────────────────────────────────────────────────────

    private function svc(Request $request): StatisticsService
    {
        return new StatisticsService($this->getFilters($request));
    }

    /**
     * Si el usuario no es admin, restringe los filtros a sus propios eventos.
     * Retorna null si el usuario no tiene ningún evento (resultado = ceros).
     */
    private function scopeToUser(array $filters, ?User $user): ?array
    {
        if (! $user || $user->role === 'admin') {
            return $filters;
        }

        $userEventIds = Event::where('user_id', $user->id)->pluck('id')->toArray();

        if (empty($userEventIds)) {
            return null;
        }

        if (! empty($filters['eventIds'])) {
            $intersection = array_values(array_intersect($filters['eventIds'], $userEventIds));

            return empty($intersection) ? null : array_merge($filters, ['eventIds' => $intersection]);
        }

        return array_merge($filters, ['eventIds' => $userEventIds]);
    }
}
