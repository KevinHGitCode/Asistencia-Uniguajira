{{--
    Contenedor reutilizable de un grupo de eventos con búsqueda integrada (ADR-0012).

    Recibe la colección de eventos a incluir y un buscador de cliente filtra las
    tarjetas de ESE grupo por título / ubicación / dependencia / creador (sin acentos).

    Props:
      - events          (Collection) eventos ya filtrados/ordenados por el llamador
      - title           (string)     encabezado del grupo
      - empty           (string)     mensaje cuando el grupo no tiene eventos
      - emptyHint       (string)     texto secundario del estado vacío
      - searchable      (bool)       muestra el buscador (def. true)
      - from / userId / badge / showCreator / showEndedBadge → se pasan a <x-events.card>

    Slot 'icon' (opcional): SVG que acompaña al título.
--}}
@props([
    'events',
    'title',
    'empty' => 'No hay eventos',
    'emptyHint' => null,
    'searchable' => true,
    'from' => null,
    'userId' => null,
    'badge' => null,
    'showCreator' => false,
    'showEndedBadge' => true,
])

@if($events->isEmpty())
    {{-- Estado vacío: fila compacta (título pequeño + mensaje al lado), poca altura. --}}
    <div class="relative flex w-full flex-wrap items-center gap-x-2 gap-y-0.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 px-5 py-3">
        <h2 class="flex items-center gap-1.5 text-base font-semibold text-gray-600 dark:text-gray-300 [&>svg]:!size-5 [&>svg]:!mr-0">
            {{ $icon ?? '' }}
            <span>{{ $title }}</span>
        </h2>
        <span class="text-sm text-gray-400 dark:text-gray-500">
            — {{ $empty }}@if($emptyHint)<span class="hidden sm:inline"> · {{ $emptyHint }}</span>@endif
        </span>
    </div>
@else
    <div
        x-data="eventsGroup()"
        x-init="init()"
        class="relative flex w-full flex-1 flex-col gap-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 p-6"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-1">
            <h2 class="flex items-center text-2xl font-bold text-gray-900 dark:text-white">
                {{ $icon ?? '' }}
                <span>{{ $title }}</span>
            </h2>

            <div class="flex items-center gap-3">
                @if($searchable)
                    <div class="relative w-full sm:w-56">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" />
                        </svg>
                        <input
                            type="text"
                            x-model="q"
                            x-on:input="apply()"
                            placeholder="Buscar en este grupo…"
                            class="w-full rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                @endif

                <span class="whitespace-nowrap px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl"
                      x-text="countLabel">{{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-ref="grid">
            @foreach($events as $event)
                <x-events.card
                    :event="$event"
                    :from="$from"
                    :user-id="$userId"
                    :badge="$badge"
                    :show-creator="$showCreator"
                    :show-ended-badge="$showEndedBadge" />
            @endforeach
        </div>

        {{-- Sin resultados al filtrar --}}
        <div x-show="visibleCount === 0" x-cloak class="text-center py-6 text-gray-500 dark:text-gray-400">
            No se encontraron eventos para “<span class="font-medium" x-text="q"></span>”.
        </div>
    </div>
@endif
