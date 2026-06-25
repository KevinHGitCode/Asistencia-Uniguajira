{{--
    Tarjeta de evento reutilizable (ADR-0012).

    Unifica el markup que estaba duplicado en events/list y users/information.

    Props:
      - event           (Event)  modelo del evento
      - from            (string) origen para el breadcrumb del detalle (p. ej. 'usuario'); null = por defecto
      - userId          (int)    user_id del origen, cuando from = 'usuario'
      - badge           (string) etiqueta opcional arriba a la derecha (p. ej. 'Dependencia')
      - showCreator     (bool)   muestra "Creado por: …" (def. false)
      - showEndedBadge  (bool)   muestra "Finalizado manualmente" si aplica (def. true)

    Cada tarjeta expone `data-event-card` y `data-search` (texto buscable) para que
    el contenedor <x-events.group> la filtre del lado del cliente.
--}}
@props([
    'event',
    'from' => null,
    'userId' => null,
    'badge' => null,
    'showCreator' => false,
    'showEndedBadge' => true,
])
@php
    $params = ['id' => $event->id];
    if ($from) {
        $params['from'] = $from;
    }
    if ($userId) {
        $params['user_id'] = $userId;
    }

    // Texto buscable (el contenedor normaliza acentos al filtrar).
    $searchText = trim(collect([
        $event->title,
        $event->location,
        $event->dependency?->name,
        $showCreator ? $event->user?->name : null,
    ])->filter()->implode(' '));

    // Datos para los filtros estructurados de cliente (ADR-0012).
    $cardDate = \Carbon\Carbon::parse($event->date)->format('Y-m-d');
    $cardStatus = $event->hasNotStarted()
        ? 'proximo'
        : ($event->isOpenForAttendance() ? 'abierto' : 'finalizado');
@endphp

<a href="{{ route('events.show', $params) }}"
   data-event-card
   data-search="{{ $searchText }}"
   data-date="{{ $cardDate }}"
   data-status="{{ $cardStatus }}"
   data-dependency="{{ $event->dependency?->name }}"
   data-area="{{ $event->area?->name }}"
   data-creator="{{ $event->user?->name }}"
   class="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">

    <div class="flex items-start justify-between mb-2 gap-2">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
            {{ $event->title }}
        </h3>

        <div class="ml-2 flex shrink-0 flex-col items-end gap-1">
            @if($badge)
                <span class="px-2 py-1 text-xs font-medium bg-green-900 text-white rounded">
                    {{ $badge }}
                </span>
            @endif

            @if($showEndedBadge && $event->ended_at !== null)
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9.563C9 9.252 9.252 9 9.563 9h4.874c.311 0 .563.252.563.563v4.874c0 .311-.252.563-.563.563H9.564A.562.562 0 0 1 9 14.437V9.564Z"/>
                    </svg>
                    Finalizado manualmente
                </span>
            @endif
        </div>
    </div>

    @if($showCreator && $event->user)
        <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">
            Creado por: {{ $event->user->name }}
        </div>
    @endif

    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
        <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
        </div>

        @if($event->start_time)
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
            </div>
        @endif

        @if($event->location)
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="line-clamp-1">{{ $event->location }}</span>
            </div>
        @endif
    </div>

    @if($event->dependency)
        <div class="flex items-center text-xs mt-2">
            <span class="px-2 py-1 bg-[#cc5e50] text-white rounded">
                {{ $event->dependency->name }}
            </span>
        </div>
    @endif

    @if($event->description)
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
            {{ $event->description }}
        </p>
    @endif
</a>
