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
        $userIds = $request->input('userIds', []);
        
        // Asegurar que sean arrays
        if (!is_array($dependencyIds)) {
            $dependencyIds = $dependencyIds ? [$dependencyIds] : [];
        }
        if (!is_array($userIds)) {
            $userIds = $userIds ? [$userIds] : [];
        }
        
        return [
            'dateFrom' => $request->input('dateFrom'),
            'dateTo' => $request->input('dateTo'),
            'dependencyIds' => array_filter($dependencyIds), // Remover valores vacíos
            'userIds' => array_filter($userIds), // Remover valores vacíos
        ];
    }
}

