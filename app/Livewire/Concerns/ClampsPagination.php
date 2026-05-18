<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait ClampsPagination
{
    protected function paginateAndClamp(Builder $query, int $perPage = 25, string $pageName = 'page'): LengthAwarePaginator
    {
        $results = $query->paginate($perPage, ['*'], $pageName);

        if ($results->currentPage() > $results->lastPage()) {
            $this->setPage($results->lastPage(), $pageName);
            $results = $query->paginate($perPage, ['*'], $pageName);
        }

        return $results;
    }
}
