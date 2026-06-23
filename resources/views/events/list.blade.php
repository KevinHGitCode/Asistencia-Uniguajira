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
