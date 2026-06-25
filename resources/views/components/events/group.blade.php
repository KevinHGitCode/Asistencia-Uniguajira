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
    @php
        $depOptions     = $events->map(fn ($e) => $e->dependency?->name)->filter()->unique()->sort()->values();
        $areaOptions    = $events->map(fn ($e) => $e->area?->name)->filter()->unique()->sort()->values();
        $creatorOptions = $events->map(fn ($e) => $e->user?->name)->filter()->unique()->sort()->values();
        $statusSet      = $events->map(fn ($e) => $e->hasNotStarted() ? 'proximo' : ($e->isOpenForAttendance() ? 'abierto' : 'finalizado'))->unique()->values();
        $statusLabels   = ['abierto' => 'Abierto', 'proximo' => 'Próximo', 'finalizado' => 'Finalizado'];
        $selectClass    = 'px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500';
    @endphp
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
                            placeholder="Buscar en este grupo…"
                            class="w-full rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                @endif

                <button type="button" @click="filtersOpen = !filtersOpen"
                    :class="filtersOpen ? 'border-blue-400 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-900/30 dark:text-blue-300' : 'border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700'"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors cursor-pointer shrink-0">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M7 12h10M10.5 18h3" /></svg>
                    Filtros
                    <span x-show="activeFilterCount > 0" x-cloak x-text="activeFilterCount"
                          class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-blue-600 text-white text-[11px] font-semibold leading-none"></span>
                </button>

                <span class="whitespace-nowrap px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl"
                      x-text="countLabel">{{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}</span>
            </div>
        </div>

        {{-- Panel de filtros (colapsable, ADR-0012) --}}
        <div x-show="filtersOpen" x-cloak
             class="rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white/60 dark:bg-zinc-800/40 p-3">
            <div class="flex items-center justify-end mb-2.5">
                <button type="button" x-show="activeFilterCount > 0 || q" x-cloak @click="resetFilters()"
                    class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400 transition-colors">
                    Limpiar filtros
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Desde</label>
                    <input type="date" x-model="dateFrom" class="{{ $selectClass }}" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Hasta</label>
                    <input type="date" x-model="dateTo" class="{{ $selectClass }}" />
                </div>
                @if($statusSet->count() > 1)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Estado</label>
                        <select x-model="status" class="{{ $selectClass }}">
                            <option value="">Todos</option>
                            @foreach($statusSet as $s)
                                <option value="{{ $s }}">{{ $statusLabels[$s] ?? ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($depOptions->count() > 1)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Dependencia</label>
                        <select x-model="dependency" class="{{ $selectClass }}">
                            <option value="">Todas</option>
                            @foreach($depOptions as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($areaOptions->count() > 1)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Área</label>
                        <select x-model="area" class="{{ $selectClass }}">
                            <option value="">Todas</option>
                            @foreach($areaOptions as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($creatorOptions->count() > 1)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Creador</label>
                        <select x-model="creator" class="{{ $selectClass }}">
                            <option value="">Todos</option>
                            @foreach($creatorOptions as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
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
            <span x-show="q && activeFilterCount === 0">No se encontraron eventos para “<span class="font-medium" x-text="q"></span>”.</span>
            <span x-show="!(q && activeFilterCount === 0)">No se encontraron eventos con los criterios aplicados.</span>
        </div>
    </div>
@endif
