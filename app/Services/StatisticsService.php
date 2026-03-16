<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de estadísticas.
 *
 * Todos los métodos leen de la misma fuente de verdad:
 *   attendances → events → attendance_details
 *
 * attendance_details guarda los datos demográficos del momento exacto del
 * registro (gender, priority_group, program_id, participant_type_id), por lo
 * que las estadísticas reflejan la realidad de cada evento, no el estado
 * actual del participante.
 */
class StatisticsService
{
    public function __construct(private readonly array $filters) {}

    // ── Contadores ──────────────────────────────────────────────────────────

    public function totalEvents(): int
    {
        return $this->eventsBase()->count();
    }

    public function totalAttendances(): int
    {
        return $this->attendancesBase()->count();
    }

    public function totalParticipants(): int
    {
        return $this->attendancesBase()
            ->distinct()
            ->count('attendances.participant_id');
    }

    // ── Por programa (attendance_details.program_id → programs) ────────────

    public function attendancesByProgram(): Collection
    {
        return $this->detailsBase()
            ->join('programs', 'attendance_details.program_id', '=', 'programs.id')
            ->select('programs.name as name', DB::raw('COUNT(*) as value'))
            ->groupBy('programs.name')
            ->orderByDesc('value')
            ->get();
    }

    public function participantsByProgram(): Collection
    {
        return $this->detailsBase()
            ->join('programs', 'attendance_details.program_id', '=', 'programs.id')
            ->select('programs.name as name', DB::raw('COUNT(DISTINCT attendances.participant_id) as value'))
            ->groupBy('programs.name')
            ->orderByDesc('value')
            ->get();
    }

    // ── Por estamento (attendance_details.participant_type_id → participant_types) ─

    public function attendancesByType(): Collection
    {
        return $this->detailsBase()
            ->join('participant_types', 'attendance_details.participant_type_id', '=', 'participant_types.id')
            ->select('participant_types.name as name', DB::raw('COUNT(*) as value'))
            ->groupBy('participant_types.name')
            ->orderByDesc('value')
            ->get();
    }

    public function participantsByType(): Collection
    {
        return $this->detailsBase()
            ->join('participant_types', 'attendance_details.participant_type_id', '=', 'participant_types.id')
            ->select('participant_types.name as name', DB::raw('COUNT(DISTINCT attendances.participant_id) as value'))
            ->groupBy('participant_types.name')
            ->orderByDesc('value')
            ->get();
    }

    // ── Por campo demográfico en attendance_details ─────────────────────────

    /**
     * Agrupa asistencias por un campo de attendance_details (gender, priority_group).
     * Los valores nulos aparecen como $fallback.
     */
    public function attendancesByDetailField(string $col, string $fallback = 'Sin datos'): Collection
    {
        return $this->detailsBase()
            ->select(
                DB::raw("COALESCE(attendance_details.{$col}, '{$fallback}') as name"),
                DB::raw('COUNT(*) as value')
            )
            ->groupBy("attendance_details.{$col}")
            ->orderByDesc('value')
            ->get();
    }

    /**
     * Cuenta participantes únicos por un campo de attendance_details.
     */
    public function participantsByDetailField(string $col, string $fallback = 'Sin datos'): Collection
    {
        return $this->detailsBase()
            ->select(
                DB::raw("COALESCE(attendance_details.{$col}, '{$fallback}') as name"),
                DB::raw('COUNT(DISTINCT attendances.participant_id) as value')
            )
            ->groupBy("attendance_details.{$col}")
            ->orderByDesc('value')
            ->get();
    }

    // ── Top listas ──────────────────────────────────────────────────────────

    public function topEvents(int $limit = 5): Collection
    {
        return $this->attendancesBase()
            ->select('events.title as name', DB::raw('COUNT(*) as value'))
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('value')
            ->limit($limit)
            ->get();
    }

    public function topParticipants(int $limit = 5): Collection
    {
        $concat = $this->concatFullName();

        return $this->attendancesBase()
            ->join('participants', 'attendances.participant_id', '=', 'participants.id')
            ->select(DB::raw("{$concat} as name"), DB::raw('COUNT(*) as value'))
            ->groupBy('participants.id', 'participants.first_name', 'participants.last_name')
            ->orderByDesc('value')
            ->limit($limit)
            ->get();
    }

    public function topUsers(int $limit = 5): Collection
    {
        return $this->attendancesBase()
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.name as name', DB::raw('COUNT(*) as value'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('value')
            ->limit($limit)
            ->get();
    }

    // ── Series temporales ───────────────────────────────────────────────────

    public function eventsOverTime(): Collection
    {
        return $this->eventsBase()
            ->select(DB::raw('DATE(date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy(DB::raw('DATE(date)'))
            ->get();
    }

    public function attendancesOverTime(): Collection
    {
        return $this->attendancesBase()
            ->select(DB::raw('DATE(events.date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(events.date)'))
            ->orderBy(DB::raw('DATE(events.date)'))
            ->get();
    }

    // ── Eventos por rol/nombre del usuario (creador del evento) ─────────────

    public function eventsByUserRole(): Collection
    {
        return $this->eventsBase()
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.role as name', DB::raw('COUNT(*) as value'))
            ->groupBy('users.role')
            ->orderByDesc('value')
            ->get();
    }

    public function eventsByUser(): Collection
    {
        return $this->eventsBase()
            ->join('users', 'events.user_id', '=', 'users.id')
            ->select('users.name as name', DB::raw('COUNT(*) as value'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('value')
            ->get();
    }

    // ── Constructores de base query ─────────────────────────────────────────

    /** Query base sobre la tabla events con todos los filtros aplicados. */
    private function eventsBase(): Builder
    {
        $q = DB::table('events');

        if (!empty($this->filters['dateFrom'])) {
            $q->where('date', '>=', $this->filters['dateFrom']);
        }
        if (!empty($this->filters['dateTo'])) {
            $q->where('date', '<=', $this->filters['dateTo']);
        }
        if (!empty($this->filters['eventIds'])) {
            $q->whereIn('id', $this->filters['eventIds']);
        }
        if (!empty($this->filters['userIds'])) {
            $q->whereIn('user_id', $this->filters['userIds']);
        }
        if (!empty($this->filters['dependencyIds'])) {
            $depUserIds = DB::table('users')
                ->whereIn('dependency_id', $this->filters['dependencyIds'])
                ->pluck('id');
            $q->whereIn('user_id', $depUserIds);
        }

        return $q;
    }

    /** attendances JOIN events con filtros. Base para conteos y top-listas. */
    private function attendancesBase(): Builder
    {
        $q = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id');

        return $this->applyEventFilters($q);
    }

    /**
     * attendances JOIN events LEFT JOIN attendance_details con filtros.
     * Base para estadísticas demográficas (gender, priority_group, program, type).
     * Se usa LEFT JOIN para no perder asistencias sin detalle en totales.
     * Los JOINs adicionales (programs, participant_types) filtran implícitamente
     * las filas donde el FK es NULL.
     */
    private function detailsBase(): Builder
    {
        $q = DB::table('attendances')
            ->join('events', 'attendances.event_id', '=', 'events.id')
            ->leftJoin('attendance_details', 'attendances.id', '=', 'attendance_details.attendance_id');

        return $this->applyEventFilters($q);
    }

    /** Aplica los filtros de fecha, eventIds, userIds y dependencyIds a queries con JOIN events. */
    private function applyEventFilters(Builder $q): Builder
    {
        if (!empty($this->filters['dateFrom'])) {
            $q->where('events.date', '>=', $this->filters['dateFrom']);
        }
        if (!empty($this->filters['dateTo'])) {
            $q->where('events.date', '<=', $this->filters['dateTo']);
        }
        if (!empty($this->filters['eventIds'])) {
            $q->whereIn('events.id', $this->filters['eventIds']);
        }
        if (!empty($this->filters['userIds'])) {
            $q->whereIn('events.user_id', $this->filters['userIds']);
        }
        if (!empty($this->filters['dependencyIds'])) {
            $depUserIds = DB::table('users')
                ->whereIn('dependency_id', $this->filters['dependencyIds'])
                ->pluck('id');
            $q->whereIn('events.user_id', $depUserIds);
        }

        return $q;
    }

    private function concatFullName(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "(participants.first_name || ' ' || participants.last_name)"
            : "CONCAT(participants.first_name, ' ', participants.last_name)";
    }
}
