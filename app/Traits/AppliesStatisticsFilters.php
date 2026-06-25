<?php

namespace App\Traits;

use App\Services\StatisticsFilterResolver;
use Illuminate\Http\Request;

trait AppliesStatisticsFilters
{
    /**
     * Parsea y normaliza los filtros del request HTTP.
     *
     * @return array<string, mixed>
     */
    protected function getFilters(Request $request): array
    {
        return StatisticsFilterResolver::filtersFromRequest($request);
    }
}
