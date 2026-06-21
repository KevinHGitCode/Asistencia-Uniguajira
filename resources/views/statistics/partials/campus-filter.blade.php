@php
    $user = auth()->user();
    $campuses = \App\Http\Controllers\StatisticsController::campusOptions();
    $activeCampusId = app(\App\Services\CampusScopeService::class)->activeCampusId($user);
@endphp

@if ($user?->isSuperadmin())
    <form method="POST" action="{{ route('statistics.campus') }}" class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-end">
        @csrf
        <label for="statistics-campus-id" class="text-xs font-medium text-gray-500 dark:text-gray-400">
            Sede activa
        </label>
        <select
            id="statistics-campus-id"
            name="campus_id"
            onchange="this.form.submit()"
            class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-200 sm:w-56"
        >
            <option value="">Todas las sedes</option>
            @foreach ($campuses as $campusId => $campusName)
                <option value="{{ $campusId }}" @selected((int) $activeCampusId === (int) $campusId)>
                    {{ $campusName }}
                </option>
            @endforeach
        </select>
    </form>
@endif
