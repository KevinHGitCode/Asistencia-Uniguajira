<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AppliesStatisticsFilters
{
    /**
     * Obtiene los filtros del request
     */
    protected function getFilters(Request $request): array
    {
        $dependencyIds = $request->input('dependencyIds', []);
        $userIds       = $request->input('userIds', []);
        $eventIds      = $request->input('eventIds', []);

        // Asegurar que sean arrays
        if (!is_array($dependencyIds)) {
            $dependencyIds = $dependencyIds ? [$dependencyIds] : [];
        }
        if (!is_array($userIds)) {
            $userIds = $userIds ? [$userIds] : [];
        }
        if (!is_array($eventIds)) {
            $eventIds = $eventIds ? [$eventIds] : [];
        }

        return [
            'dateFrom'      => $request->input('dateFrom'),
            'dateTo'        => $request->input('dateTo'),
            'dependencyIds' => array_filter($dependencyIds),
            'userIds'       => array_filter($userIds),
            'eventIds'      => array_filter(array_map('intval', $eventIds)),
        ];
    }

    /**
     * Aplica filtro por IDs de eventos específicos a una query.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param array  $filters
     * @param string $column  columna de ID del evento (ej: 'events.id', 'attendances.event_id')
     */
    protected function applyEventIds($query, array $filters, string $column = 'events.id'): void
    {
        if (!empty($filters['eventIds'])) {
            $query->whereIn($column, $filters['eventIds']);
        }
    }
}

