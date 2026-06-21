<div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

    {{-- Header: título + paginación compacta + búsqueda --}}
    <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Dependencias</h2>

        <div class="flex items-center gap-3 ml-auto">

            @if(auth()->user()?->isSuperadmin())
                <select wire:model.live="campusId"
                        class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-[#cc5e50] focus:ring-[#cc5e50] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-200">
                    <option value="">Todas las sedes</option>
                    @foreach($campuses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            @endif

            {{-- Paginación superior: < X/Y > --}}
            @if($dependencies->hasPages())
            <div class="flex items-center gap-0.5">
                <button wire:click="previousPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled($dependencies->onFirstPage())
                        class="p-1.5 rounded-lg transition-colors {{ $dependencies->onFirstPage() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
                        title="Página anterior">
                    <flux:icon.chevron-left class="size-4" />
                </button>

                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 px-1.5 tabular-nums select-none">
                    {{ $dependencies->currentPage() }}&thinsp;/&thinsp;{{ $dependencies->lastPage() }}
                </span>

                <button wire:click="nextPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled(!$dependencies->hasMorePages())
                        class="p-1.5 rounded-lg transition-colors {{ !$dependencies->hasMorePages() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
                        title="Página siguiente">
                    <flux:icon.chevron-right class="size-4" />
                </button>
            </div>
            @endif

            {{-- Búsqueda reactiva (Livewire, sin Alpine) --}}
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input wire:model.live.debounce.350ms="search"
                       type="text"
                       placeholder="Buscar..."
                       class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition w-40 sm:w-56" />
            </div>

        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,previousPage,nextPage,gotoPage">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">Nombre</th>
                    @if(auth()->user()?->isSuperadmin())
                        <th class="px-4 sm:px-6 py-3 text-left font-medium hidden md:table-cell">Sede</th>
                    @endif
                    <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Áreas</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Eventos</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium">Creada</th>
                    <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse($dependencies as $dependency)
                    <tr wire:key="dependency-{{ $dependency->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                            {{ ($dependencies->currentPage() - 1) * $dependencies->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                                    <flux:icon.building-office-2 class="size-6 text-[#cc5e50]" />
                                </div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $dependency->name }}</span>
                            </div>
                        </td>
                        @if(auth()->user()?->isSuperadmin())
                            <td class="px-4 sm:px-6 py-4 text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $dependency->campus?->name ?? 'Sin sede' }}
                            </td>
                        @endif
                        <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#62a9b6]">
                                {{ $dependency->areas_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#e2a542]">
                                {{ $dependency->events_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#2563eb]">
                                {{ $dependency->participants_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                            {{ $dependency->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEdit({{ $dependency->id }}, {{ Js::from($dependency->name) }}, {{ Js::from((string) $dependency->campus_id) }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                    title="Editar">
                                    <flux:icon.pencil-square class="size-4" />
                                </button>
                                <button
                                    @click="openDelete({{ $dependency->id }}, {{ Js::from($dependency->name) }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                    title="Eliminar">
                                    <flux:icon.trash class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                <flux:icon.building-office-2 class="size-12 opacity-30" />
                                <p class="text-sm">
                                    @if($search)
                                        No se encontraron dependencias para
                                        <span class="font-medium text-gray-600 dark:text-gray-300">"{{ $search }}"</span>.
                                    @else
                                        No hay dependencias registradas aún.
                                    @endif
                                </p>
                                @unless($search)
                                <button @click="openCreate()"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                    Crear la primera dependencia
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación inferior --}}
    @if($dependencies->hasPages())
    @php
        $current = $dependencies->currentPage();
        $last    = $dependencies->lastPage();
        $left    = max(1, $current - 2);
        $right   = min($last, $current + 2);
    @endphp
    <div class="px-4 sm:px-6 py-4 border-t border-neutral-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <p class="text-xs text-gray-500 dark:text-gray-400">
            Mostrando
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $dependencies->firstItem() }}</span>–<span class="font-medium text-gray-700 dark:text-gray-300">{{ $dependencies->lastItem() }}</span>
            de
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $dependencies->total() }}</span>
            {{ Str::plural('dependencia', $dependencies->total()) }}
        </p>

        <div class="flex items-center gap-1">

            {{-- Anterior --}}
            @if($dependencies->onFirstPage())
                <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 dark:text-zinc-600 cursor-not-allowed select-none">Anterior</span>
            @else
                <button wire:click="previousPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                    Anterior
                </button>
            @endif

            {{-- Primera + ellipsis --}}
            @if($left > 1)
                <button wire:click="gotoPage(1)"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">1</button>
                @if($left > 2)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                @endif
            @endif

            {{-- Ventana de páginas --}}
            @for($p = $left; $p <= $right; $p++)
                @if($p === $current)
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-white bg-[#cc5e50] select-none">{{ $p }}</span>
                @else
                    <button wire:click="gotoPage({{ $p }})"
                            wire:loading.attr="disabled"
                            wire:target="previousPage,nextPage,gotoPage"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $p }}</button>
                @endif
            @endfor

            {{-- Última + ellipsis --}}
            @if($right < $last)
                @if($right < $last - 1)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                @endif
                <button wire:click="gotoPage({{ $last }})"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $last }}</button>
            @endif

            {{-- Siguiente --}}
            @if($dependencies->hasMorePages())
                <button wire:click="nextPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                    Siguiente
                </button>
            @else
                <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 dark:text-zinc-600 cursor-not-allowed select-none">Siguiente</span>
            @endif

        </div>
    </div>
    @endif

</div>
