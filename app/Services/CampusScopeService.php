<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CampusScopeService
{
    public const SESSION_KEY = 'active_campus_id';

    public function isSuperadmin(?User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) $user?->isSuperadmin();
    }

    public function selectedCampusId(): ?int
    {
        $campusId = session(self::SESSION_KEY);

        return $campusId !== null && $campusId !== '' ? (int) $campusId : null;
    }

    public function activeCampusId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->isSuperadmin()) {
            return $this->selectedCampusId();
        }

        return $user->campus_id !== null ? (int) $user->campus_id : null;
    }

    /**
     * @template TQuery of \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    public function applyToQuery(EloquentBuilder|QueryBuilder $query, ?User $user = null, string $column = 'campus_id')
    {
        $user ??= auth()->user();
        $campusId = $this->activeCampusId($user);

        if ($campusId === null) {
            return $query;
        }

        $query->where($column, $campusId);

        return $query;
    }

    public function canAccessCampus(?User $user, ?int $campusId): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperadmin()) {
            $activeCampusId = $this->selectedCampusId();

            return $activeCampusId === null || $campusId === $activeCampusId;
        }

        return $campusId !== null && (int) $user->campus_id === $campusId;
    }

    public function canAccessResource(?User $user, ?object $resource, string $column = 'campus_id'): bool
    {
        $user ??= auth()->user();

        if (! $resource || ! isset($resource->{$column})) {
            return false;
        }

        $campusId = $resource->{$column};

        return $this->canAccessCampus($user, $campusId !== null ? (int) $campusId : null);
    }

    public function authorizeResource(?User $user, ?object $resource, string $column = 'campus_id'): void
    {
        $user ??= auth()->user();

        abort_unless($this->canAccessResource($user, $resource, $column), 403, 'No tienes acceso a esta sede.');
    }
}
