<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsFilterResolver
{
    public function __construct(private readonly CampusScopeService $campusScope) {}

    /** @return array<string, mixed> */
    public static function filtersFromRequest(Request $request): array
    {
        $toIntArray = static function (mixed $value): array {
            $values = is_array($value) ? $value : [$value];

            return array_values(array_unique(array_filter(array_map('intval', $values))));
        };

        return [
            'dateFrom' => $request->input('dateFrom') ?: null,
            'dateTo' => $request->input('dateTo') ?: null,
            'campusIds' => $toIntArray($request->input('campusIds')),
            'allCampuses' => $request->boolean('allCampuses'),
            'dependencyIds' => $toIntArray($request->input('dependencyIds')),
            'hasDependencyFilter' => $request->has('dependencyIds'),
            'onlyOwnEvents' => $request->boolean('onlyOwnEvents'),
            'userIds' => $toIntArray($request->input('userIds')),
            'eventIds' => $toIntArray($request->input('eventIds')),
        ];
    }

    /** @return array<string, mixed> */
    public function resolve(array $filters, ?User $user): array
    {
        if (! $user) {
            return $this->noResults($filters);
        }

        $campusIds = $this->resolvedCampusIds($filters, $user);
        $allowedDependencyIds = $this->allowedDependencies($user, $campusIds);
        $selectedDependencyIds = array_values(array_intersect(
            $filters['dependencyIds'] ?? [],
            $allowedDependencyIds
        ));

        $filters['campusIds'] = $campusIds;
        $filters['dependencyIds'] = $selectedDependencyIds;
        $filters['actorUserId'] = $user->id;

        if ($user->hasAdminAccess()) {
            return $filters;
        }

        $dependencyIdsForVisibility = ($filters['hasDependencyFilter'] ?? false)
            ? $selectedDependencyIds
            : $allowedDependencyIds;

        $eventQuery = Event::query()->select('events.id');
        if ($campusIds !== []) {
            $eventQuery->whereIn('events.campus_id', $campusIds);
        }

        if ($filters['onlyOwnEvents']) {
            $eventQuery->where('events.user_id', $user->id);
        } else {
            $eventQuery->where(function ($query) use ($user, $dependencyIdsForVisibility): void {
                $query->where('events.user_id', $user->id);

                if ($dependencyIdsForVisibility !== []) {
                    $query->orWhereIn('events.dependency_id', $dependencyIdsForVisibility);
                }
            });
        }

        $visibleEventIds = $eventQuery->pluck('id')->all();
        if (($filters['eventIds'] ?? []) !== []) {
            $visibleEventIds = array_values(array_intersect($visibleEventIds, $filters['eventIds']));
        }

        // Para usuarios normales, los IDs ya codifican la combinación segura
        // de sede, dependencias permitidas y "Solo mis eventos".
        $filters['eventIds'] = $visibleEventIds !== [] ? $visibleEventIds : [-1];
        $filters['dependencyIds'] = [];
        $filters['onlyOwnEvents'] = false;

        return $filters;
    }

    /** Aplica los filtros ya resueltos a una consulta que usa events como tabla base. */
    public function applyToEventsQuery($query, array $filters): void
    {
        if (($filters['campusIds'] ?? []) !== []) {
            $query->whereIn('events.campus_id', $filters['campusIds']);
        }
        if (! empty($filters['dateFrom'])) {
            $query->where('events.date', '>=', $filters['dateFrom']);
        }
        if (! empty($filters['dateTo'])) {
            $query->where('events.date', '<=', $filters['dateTo']);
        }
        if (($filters['eventIds'] ?? []) !== []) {
            $query->whereIn('events.id', $filters['eventIds']);
        }
        if (($filters['dependencyIds'] ?? []) !== []) {
            $query->whereIn('events.dependency_id', $filters['dependencyIds']);
        }
        if (! empty($filters['onlyOwnEvents']) && ! empty($filters['actorUserId'])) {
            $query->where('events.user_id', $filters['actorUserId']);
        }
    }

    /** @return array{role: string, showCampuses: bool, campusIds: int[], campuses: array<int, string>, dependencies: array<int, string>} */
    public function options(array $filters, User $user): array
    {
        $campusIds = $this->resolvedCampusIds($filters, $user);
        $dependencies = Dependency::query()
            ->whereIn('id', $this->allowedDependencies($user, $campusIds))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(int) $id => $name])
            ->all();

        return [
            'role' => $user->role,
            'showCampuses' => $user->isSuperadmin(),
            'campusIds' => $campusIds,
            'campuses' => $user->isSuperadmin()
                ? Campus::orderBy('name')->pluck('name', 'id')->mapWithKeys(fn ($name, $id) => [(int) $id => $name])->all()
                : [],
            'dependencies' => $dependencies,
        ];
    }

    /** @return int[] */
    private function resolvedCampusIds(array $filters, User $user): array
    {
        if (! $user->isSuperadmin()) {
            return $user->campus_id ? [(int) $user->campus_id] : [];
        }

        $requested = $filters['campusIds'] ?? [];
        if ($filters['allCampuses'] ?? false) {
            return [];
        }

        if ($requested === []) {
            $activeCampusId = $this->campusScope->activeCampusId($user);

            return $activeCampusId ? [$activeCampusId] : [];
        }

        return Campus::whereIn('id', $requested)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** @return int[] */
    private function allowedDependencies(User $user, array $campusIds): array
    {
        if (! $user->hasAdminAccess()) {
            $user->loadMissing('dependencies');

            return $user->dependencies
                ->when($campusIds !== [], fn ($dependencies) => $dependencies->whereIn('campus_id', $campusIds))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return Dependency::query()
            ->when($campusIds !== [], fn ($query) => $query->whereIn('campus_id', $campusIds))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @return array<string, mixed> */
    private function noResults(array $filters): array
    {
        return array_merge($filters, ['eventIds' => [-1], 'campusIds' => [], 'dependencyIds' => []]);
    }
}
