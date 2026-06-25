<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Services\CampusScopeService;
use App\Services\StatisticsFilterResolver;
use App\Services\StatisticsService;
use App\Traits\AppliesStatisticsFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    use AppliesStatisticsFilters;

    public function __construct(
        private readonly CampusScopeService $campusScope,
        private readonly StatisticsFilterResolver $statisticsFilters,
    ) {}

    // ── Endpoints de resumen (1 request = todos los datos del módulo) ────────

    /**
     * Todos los datos para el módulo "Por Asistencias".
     */
    public function asistenciasSummary(Request $request): JsonResponse
    {
        $filters = $this->statisticsFilters->resolve($this->getFilters($request), Auth::user());

        $s = new StatisticsService($filters);

        return response()->json([
            'counters' => [
                'events' => $s->totalEvents(),
                'attendances' => $s->totalAttendances(),
                'participants' => $s->totalParticipants(),
            ],
            'charts' => [
                'attendancesByProgram' => $s->attendancesByProgram(),
                'attendancesByDependency' => $s->attendancesByDependency(),
                'attendancesByOrganization' => $s->attendancesByOrganization(),
                'attendancesUnclassified' => $s->attendancesUnclassified(),
                'topEvents' => $s->topEvents(),
                'topParticipants' => $s->topParticipants(),
                'byRole' => $s->attendancesByType(),
                'bySex' => $s->attendancesByDetailField('gender'),
                'byGroup' => $s->attendancesByDetailField('priority_group'),
            ],
        ]);
    }

    /**
     * Todos los datos para el módulo "Por Participantes".
     */
    public function participantesSummary(Request $request): JsonResponse
    {
        $filters = $this->statisticsFilters->resolve($this->getFilters($request), Auth::user());

        $s = new StatisticsService($filters);

        return response()->json([
            'counters' => [
                'events' => $s->totalEvents(),
                'participants' => $s->totalParticipants(),
            ],
            'charts' => [
                'participantsByProgram' => $s->participantsByProgram(),
                'participantsByDependency' => $s->participantsByDependency(),
                'participantsByOrganization' => $s->participantsByOrganization(),
                'participantsUnclassified' => $s->participantsUnclassified(),
                'byRole' => $s->participantsByTypeDedup(),
                'bySex' => $s->participantsByDetailField('gender'),
                'byGroup' => $s->participantsByGroupDedup(),
            ],
        ]);
    }

    // ── Endpoints individuales (compatibilidad con rutas existentes) ─────────

    public function totalEvents(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->totalEvents());
    }

    public function totalAttendances(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->totalAttendances());
    }

    public function totalParticipants(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->totalParticipants());
    }

    public function attendancesByProgram(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByProgram());
    }

    public function participantsByProgram(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByProgram());
    }

    public function eventsOverTime(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->eventsOverTime());
    }

    public function attendancesOverTime(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesOverTime());
    }

    public function topEvents(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->topEvents());
    }

    public function topParticipants(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->topParticipants());
    }

    public function topUsers(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->topUsers());
    }

    public function eventsByRole(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->eventsByUserRole());
    }

    public function eventsByUser(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->eventsByUser());
    }

    public function attendancesByRole(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByType());
    }

    public function attendancesBySex(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByDetailField('gender'));
    }

    public function attendancesByGroup(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByDetailField('priority_group'));
    }

    public function participantsByRole(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByType());
    }

    public function participantsBySex(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByDetailField('gender'));
    }

    public function participantsByGroup(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByDetailField('priority_group'));
    }

    public function attendancesByDependency(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByDependency());
    }

    public function participantsByDependency(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByDependency());
    }

    public function attendancesByOrganization(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->attendancesByOrganization());
    }

    public function participantsByOrganization(Request $request): JsonResponse
    {
        return response()->json($this->svc($request)->participantsByOrganization());
    }

    public function filterOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        return response()->json(
            $this->statisticsFilters->options($this->getFilters($request), $user)
        );
    }

    public function updateCampus(Request $request)
    {
        abort_unless($request->user()?->isSuperadmin(), 403);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
        ]);

        $campusId = empty($validated['campus_id']) ? null : (int) $validated['campus_id'];

        if ($campusId === null) {
            $request->session()->forget(CampusScopeService::SESSION_KEY);
        } else {
            $request->session()->put(CampusScopeService::SESSION_KEY, $campusId);
        }

        if ($request->expectsJson()) {
            return response()->json(['campus_id' => $campusId]);
        }

        return redirect()->back();
    }

    // ── Helpers privados ────────────────────────────────────────────────────

    private function svc(Request $request): StatisticsService
    {
        return new StatisticsService(
            $this->statisticsFilters->resolve($this->getFilters($request), Auth::user())
        );
    }

    public static function campusOptions(): array
    {
        return Campus::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
