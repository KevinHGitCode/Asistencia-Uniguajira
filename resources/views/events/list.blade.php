<x-layouts.app :title="__('Lista de Eventos')">

    <x-breadcrumb class="mb-4" :items="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Eventos'],
    ]" />

    <!-- Leyenda -->
    <div class="relative flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#62a9b6]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Áreas</span>
            </div>
        </div>
    </div>

    @if(Auth::user()->isSuperadmin())
        <div class="mb-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form method="GET" action="{{ route('events.list') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                        <flux:icon.map-pin class="size-5" />
                    </div>
                    <div>
                        <label for="events-list-campus-id" class="text-sm font-semibold text-gray-900 dark:text-white">
                            Filtrar tus eventos por sede
                        </label>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Este filtro solo afecta el módulo Tus eventos y no cambia la sede activa del dashboard.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <select
                        id="events-list-campus-id"
                        name="campus_id"
                        class="w-full rounded-xl border border-neutral-200 bg-zinc-50 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:bg-zinc-900 sm:w-60">
                        <option value="">Todas mis sedes</option>
                        @foreach(($campuses ?? []) as $campusId => $campusName)
                            <option value="{{ $campusId }}" @selected((int) ($selectedCampusId ?? 0) === (int) $campusId)>
                                {{ $campusName }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-2 text-sm font-medium text-blue-600 shadow-sm transition hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 hover:cursor-pointer">
                        <flux:icon.funnel class="size-4" />
                        Filtrar
                    </button>

                    @if(($selectedCampusId ?? null) !== null)
                        <a href="{{ route('events.list') }}"
                           class="text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    @php
        $now = now();
        $eventDateTime = function ($event, ?string $time) {
            $date = \Carbon\Carbon::parse($event->date)->toDateString();

            return \Carbon\Carbon::parse($date.' '.($time ?: '00:00:00'));
        };
        $eventSortKey = fn ($event, ?string $time) => \Carbon\Carbon::parse($event->date)->toDateString().' '.($time ?: '00:00:00');

        $myEventsInProgress = $myEvents->filter(function ($event) use ($now, $eventDateTime) {
            if ($event->ended_at !== null) return false;
            $start = $eventDateTime($event, $event->start_time);
            $end = $eventDateTime($event, $event->end_time);
            return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
        })->sortBy(fn ($e) => $eventSortKey($e, $e->start_time));

        $myEventsUpcoming = $myEvents->filter(function ($event) use ($now, $eventDateTime) {
            if ($event->ended_at !== null) return false;
            return $now->lessThan($eventDateTime($event, $event->start_time));
        })->sortBy(fn ($e) => $eventSortKey($e, $e->start_time));

        $myEventsFinished = $myEvents->filter(function ($event) use ($now, $eventDateTime) {
            if ($event->ended_at !== null) return true;
            return $now->greaterThan($eventDateTime($event, $event->end_time));
        })->sortByDesc(fn ($e) => $eventSortKey($e, $e->end_time));
    @endphp

    <div class="space-y-6">

        {{-- Eventos en Proceso --}}
        <x-events.group :events="$myEventsInProgress" title="Eventos en Proceso"
            empty="No hay eventos en proceso" empty-hint="Los eventos que estén en curso aparecerán aquí">
            <x-slot:icon>
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-events.group>

        {{-- Eventos Próximos --}}
        <x-events.group :events="$myEventsUpcoming" title="Eventos Próximos"
            empty="No hay eventos próximos" empty-hint="Los eventos futuros aparecerán aquí">
            <x-slot:icon>
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </x-slot:icon>
        </x-events.group>

        {{-- Eventos Finalizados --}}
        <x-events.group :events="$myEventsFinished" title="Eventos Finalizados"
            empty="No hay eventos finalizados" empty-hint="Los eventos completados aparecerán aquí">
            <x-slot:icon>
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-events.group>

        {{-- Eventos de la dependencia del usuario autenticado --}}
        @if(Auth::user()->dependencies()->exists())
            <x-events.group
                :events="$dependencyEvents->sortByDesc(fn ($e) => $e->date.' '.$e->start_time)"
                :title="'Eventos de -> '.($dependenciesNames ?: 'mis dependencias')"
                empty="No hay eventos de la dependencia"
                empty-hint="Otros usuarios de tu dependencia aún no han creado eventos"
                badge="Dependencia"
                :show-creator="true">
                <x-slot:icon>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </x-slot:icon>
            </x-events.group>
        @endif

    </div>

</x-layouts.app>
