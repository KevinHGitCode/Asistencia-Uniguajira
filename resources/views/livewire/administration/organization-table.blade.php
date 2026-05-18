<div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

    <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Organizaciones</h2>

        <div class="flex items-center gap-3 ml-auto">
            @if($organizations->hasPages())
            <div class="flex items-center gap-0.5">
                <button wire:click="previousPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled($organizations->onFirstPage())
                        class="p-1.5 rounded-lg transition-colors {{ $organizations->onFirstPage() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
                        title="Página anterior">
                    <flux:icon.chevron-left class="size-4" />
                </button>

                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 px-1.5 tabular-nums select-none">
                    {{ $organizations->currentPage() }}&thinsp;/&thinsp;{{ $organizations->lastPage() }}
                </span>

                <button wire:click="nextPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled(!$organizations->hasMorePages())
                        class="p-1.5 rounded-lg transition-colors {{ !$organizations->hasMorePages() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
                        title="Página siguiente">
                    <flux:icon.chevron-right class="size-4" />
                </button>
            </div>
            @endif

            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input wire:model.live.debounce.350ms="search"
                       type="text"
                       placeholder="Buscar..."
                       class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] transition w-40 sm:w-56" />
            </div>
        </div>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,previousPage,nextPage,gotoPage">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">Organización</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Creado</th>
                    <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse($organizations as $organization)
                    <tr wire:key="organization-{{ $organization->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                            {{ ($organizations->currentPage() - 1) * $organizations->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center gap-3">
                                <flux:icon name="building-library" class="size-5 text-[#8b5cf6]" />
                                <span class="font-medium text-gray-900 dark:text-white">{{ $organization->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#8b5cf6]">
                                {{ $organization->participant_roles_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs hidden sm:table-cell">
                            {{ $organization->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEdit({{ $organization->id }}, {{ Js::from($organization->name) }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                    title="Editar">
                                    <flux:icon.pencil-square class="size-4" />
                                </button>

                                <button
                                    @click="openMerge({{ $organization->id }}, {{ Js::from($organization->name) }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 transition-colors cursor-pointer"
                                    title="Fusionar con...">
                                    <flux:icon.arrows-right-left class="size-4" />
                                </button>

                                @if(($organization->participant_roles_count ?? 0) === 0)
                                    <button
                                        @click="openDelete({{ $organization->id }}, {{ Js::from($organization->name) }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                        title="Eliminar">
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-zinc-600 italic">En uso</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                <flux:icon name="building-library" class="size-12 opacity-30" />
                                <p class="text-sm">
                                    @if($search)
                                        No se encontraron organizaciones para
                                        <span class="font-medium text-gray-600 dark:text-gray-300">"{{ $search }}"</span>.
                                    @else
                                        No hay organizaciones registradas aún.
                                    @endif
                                </p>
                                @unless($search)
                                <button @click="openCreate()"
                                    class="text-sm text-[#8b5cf6] hover:underline cursor-pointer">
                                    Crear la primera organización
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($organizations->hasPages())
    @php
        $current = $organizations->currentPage();
        $last = $organizations->lastPage();
        $left = max(1, $current - 2);
        $right = min($last, $current + 2);
    @endphp
    <div class="px-4 sm:px-6 py-4 border-t border-neutral-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Mostrando
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $organizations->firstItem() }}</span>–<span class="font-medium text-gray-700 dark:text-gray-300">{{ $organizations->lastItem() }}</span>
            de
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $organizations->total() }}</span>
            {{ Str::plural('organización', $organizations->total()) }}
        </p>

        <div class="flex items-center gap-1">
            @if($organizations->onFirstPage())
                <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 dark:text-zinc-600 cursor-not-allowed select-none">Anterior</span>
            @else
                <button wire:click="previousPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                    Anterior
                </button>
            @endif

            @if($left > 1)
                <button wire:click="gotoPage(1)"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">1</button>
                @if($left > 2)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                @endif
            @endif

            @for($p = $left; $p <= $right; $p++)
                @if($p === $current)
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-white bg-[#8b5cf6] select-none">{{ $p }}</span>
                @else
                    <button wire:click="gotoPage({{ $p }})"
                            wire:loading.attr="disabled"
                            wire:target="previousPage,nextPage,gotoPage"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $p }}</button>
                @endif
            @endfor

            @if($right < $last)
                @if($right < $last - 1)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                @endif
                <button wire:click="gotoPage({{ $last }})"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $last }}</button>
            @endif

            @if($organizations->hasMorePages())
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
