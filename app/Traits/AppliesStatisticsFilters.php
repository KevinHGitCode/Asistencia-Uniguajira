<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AppliesStatisticsFilters
{
    /**
     * Parsea y normaliza los filtros del request HTTP.
     *
     * @return array{dateFrom: ?string, dateTo: ?string, dependencyIds: int[], userIds: int[], eventIds: int[]}
     */
    protected function getFilters(Request $request): array
    {
        $toIntArray = function (mixed $value): array {
            if (! $value) return [];
            $arr = is_array($value) ? $value : [$value];
            return array_values(array_filter(array_map('intval', $arr)));
        };

        return [
            'dateFrom'      => $request->input('dateFrom') ?: null,
            'dateTo'        => $request->input('dateTo') ?: null,
            'dependencyIds' => $toIntArray($request->input('dependencyIds')),
            'userIds'       => $toIntArray($request->input('userIds')),
            'eventIds'      => $toIntArray($request->input('eventIds')),
        ];
    }
}
