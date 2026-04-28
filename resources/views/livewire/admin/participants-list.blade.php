<div>
    {{-- Buscador --}}
    <div class="mb-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre, documento o correo…"
                class="w-full pl-9 pr-4 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700
                       bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-gray-100
                       placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            />
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-zinc-700">
        <table class="min-w-[900px] w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Documento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Estamento(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Programa(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Dependencia(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Vinculación</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Correo</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse ($participants as $participant)
                    @php
                        $typeNames              = $participant->activeRoles->pluck('type.name')->filter()->unique()->values();
                        $primaryType            = $typeNames->first();
                        $extraTypesCount        = max(0, $typeNames->count() - 1);

                        $programNames           = $participant->activeRoles->pluck('program.name')->filter()->unique()->values();
                        $primaryProgram         = $programNames->first();
                        $extraProgramsCount     = max(0, $programNames->count() - 1);

                        $dependencyNames        = $participant->activeRoles->pluck('dependency.name')->filter()->unique()->values();
                        $primaryDependency      = $dependencyNames->first();
                        $extraDependenciesCount = max(0, $dependencyNames->count() - 1);

                        $affiliationNames       = $participant->activeRoles->pluck('affiliation.name')->filter()->unique()->values();
                        $primaryAffiliation     = $affiliationNames->first();
                        $extraAffiliationsCount = max(0, $affiliationNames->count() - 1);
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                            {{ $participant->document }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $participant->first_name }} {{ $participant->last_name }}
                            </p>
                            @if($participant->student_code)
                                <p class="text-xs text-gray-400 dark:text-zinc-500">Cód. {{ $participant->student_code }}</p>
                            @endif
                        </td>

                        {{-- Estamento(s) --}}
                        <td class="px-4 py-3">
                            @if($typeNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 max-w-[10rem] truncate" title="{{ $primaryType }}">
                                        {{ $primaryType }}
                                    </span>
                                    @if($extraTypesCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-teal-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraTypesCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-48 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Estamentos activos</p>
                                                <ul class="space-y-1">
                                                    @foreach ($typeNames as $typeName)
                                                        <li>{{ $typeName }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Programa(s) --}}
                        <td class="px-4 py-3">
                            @if($programNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[12rem]" title="{{ $primaryProgram }}">
                                        {{ $primaryProgram }}
                                    </span>
                                    @if($extraProgramsCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraProgramsCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Programas activos</p>
                                                <ul class="space-y-1">
                                                    @foreach ($programNames as $name)
                                                        <li>{{ $name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Dependencia(s) --}}
                        <td class="px-4 py-3">
                            @if($dependencyNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[12rem]" title="{{ $primaryDependency }}">
                                        {{ $primaryDependency }}
                                    </span>
                                    @if($extraDependenciesCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraDependenciesCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Dependencias activas</p>
                                                <ul class="space-y-1">
                                                    @foreach ($dependencyNames as $name)
                                                        <li>{{ $name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Vinculación --}}
                        <td class="px-4 py-3">
                            @if($affiliationNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-600 dark:text-zinc-400 truncate max-w-[10rem]" title="{{ $primaryAffiliation }}">
                                        {{ $primaryAffiliation }}
                                    </span>
                                    @if($extraAffiliationsCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-amber-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraAffiliationsCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-48 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Vinculaciones activas</p>
                                                <ul class="space-y-1">
                                                    @foreach ($affiliationNames as $affiliationName)
                                                        <li>{{ $affiliationName }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $participant->email ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                            @if($search !== '')
                                No se encontraron participantes con "<strong>{{ $search }}</strong>".
                            @else
                                Aún no hay participantes registrados.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($participants->hasPages())
        <div class="mt-4">
            {{ $participants->links() }}
        </div>
    @endif

    {{-- Contador --}}
    <p class="mt-2 text-xs text-gray-400 dark:text-zinc-500 text-right">
        {{ $participants->total() }} participante(s) en total
    </p>
</div>

@script
<script>
    Alpine.data('floatingTooltip', () => ({
        open: false,
        x: 0,
        y: 0,
        _timer: null,

        show(el) {
            clearTimeout(this._timer);
            const rect = el.getBoundingClientRect();
            let x = rect.left;
            let y = rect.bottom + 6;

            // Si se sale por la derecha, alinear al borde derecho del botón
            if (x + 240 > window.innerWidth) {
                x = rect.right - 240;
            }
            // Si queda negativo, pegarlo al borde izquierdo
            if (x < 8) {
                x = 8;
            }
            // Si se sale por abajo, mostrar encima del botón
            if (y + 150 > window.innerHeight) {
                y = rect.top - 6;
                // El tooltip se posicionará con transform para subir
                this._above = true;
            } else {
                this._above = false;
            }

            this.x = x;
            this.y = y;
            this.open = true;
        },

        keep() {
            clearTimeout(this._timer);
        },

        hide() {
            this._timer = setTimeout(() => { this.open = false; }, 150);
        },
    }));
</script>
@endscript