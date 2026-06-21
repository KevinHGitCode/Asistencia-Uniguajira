<div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

    <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Programas</h2>

        <div class="flex items-center gap-3 ml-auto">

            @if(auth()->user()?->isSuperadmin())
                <select wire:model.live="campusId"
                        class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-200">
                    <option value="">Todas las sedes</option>
                    @foreach($campuses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            @if($programs->hasPages())
            <div class="flex items-center gap-0.5">
                <button wire:click="previousPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled($programs->onFirstPage())
                        class="p-1.5 rounded-lg transition-colors {{ $programs->onFirstPage() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
                        title="Página anterior">
                    <flux:icon.chevron-left class="size-4" />
                </button>

                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 px-1.5 tabular-nums select-none">
                    {{ $programs->currentPage() }}&thinsp;/&thinsp;{{ $programs->lastPage() }}
                </span>

                <button wire:click="nextPage"
                        wire:loading.attr="disabled"
                        wire:target="previousPage,nextPage,gotoPage"
                        @disabled(!$programs->hasMorePages())
                        class="p-1.5 rounded-lg transition-colors {{ !$programs->hasMorePages() ? 'text-gray-300 dark:text-zinc-600 cursor-not-allowed' : 'text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' }}"
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
                       class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2563eb] transition w-40 sm:w-56" />
            </div>
        </div>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,previousPage,nextPage,gotoPage">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                    <th class="px-4 sm:px-6 py-3 text-left font-medium">Programa</th>
                    <th class="px-4 sm:px-6 py-3 text-left font-medium hidden lg:table-cell">Programa base</th>
                    <th class="px-4 sm:px-6 py-3 text-left font-medium hidden xl:table-cell">Lugar de oferta</th>
                    @if(auth()->user()?->isSuperadmin())
                        <th class="px-4 sm:px-6 py-3 text-left font-medium hidden md:table-cell">Sede</th>
                    @endif
                    <th class="px-4 sm:px-6 py-3 text-left font-medium hidden sm:table-cell">Tipo</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                    <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Creado</th>
                    <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse($programs as $program)
                    <tr wire:key="program-{{ $program->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                            {{ ($programs->currentPage() - 1) * $programs->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center gap-3">
                                <flux:icon.book-open class="size-5 text-[#2563eb]" />
                                <span class="font-medium text-gray-900 dark:text-white">{{ $program->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-gray-500 dark:text-gray-400 hidden lg:table-cell">
                            {{ $program->academicProgram?->name ?? '—' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-gray-500 dark:text-gray-400 hidden xl:table-cell">
                            {{ $program->offer_location ?? '—' }}
                        </td>
                        @if(auth()->user()?->isSuperadmin())
                            <td class="px-4 sm:px-6 py-4 text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $program->campus?->name ?? 'Sin sede' }}
                            </td>
                        @endif
                        <td class="px-4 sm:px-6 py-4 text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                            {{ $program->program_type ?? '—' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#2563eb]">
                                {{ $program->participants_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs hidden sm:table-cell">
                            {{ $program->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEdit({{ $program->id }}, {{ Js::from($program->academicProgram?->name ?? $program->name) }}, {{ Js::from($program->program_type ?? '') }}, {{ Js::from((string) $program->campus_id) }}, {{ Js::from((string) $program->academic_program_id) }}, {{ Js::from($program->offer_location ?? '') }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                    title="Editar">
                                    <flux:icon.pencil-square class="size-4" />
                                </button>

                                @if(($program->participants_count ?? 0) === 0)
                                    <button
                                        @click="openDelete({{ $program->id }}, {{ Js::from($program->name) }})"
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
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                <flux:icon.book-open class="size-12 opacity-30" />
                                <p class="text-sm">
                                    @if($search)
                                        No se encontraron programas para
                                        <span class="font-medium text-gray-600 dark:text-gray-300">"{{ $search }}"</span>.
                                    @else
                                        No hay programas registrados aún.
                                    @endif
                                </p>
                                @unless($search)
                                <button @click="openCreate()"
                                    class="text-sm text-[#2563eb] hover:underline cursor-pointer">
                                    Crear el primer programa
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($programs->hasPages())
    @php
        $current = $programs->currentPage();
        $last = $programs->lastPage();
        $left = max(1, $current - 2);
        $right = min($last, $current + 2);
    @endphp
    <div class="px-4 sm:px-6 py-4 border-t border-neutral-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Mostrando
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $programs->firstItem() }}</span>–<span class="font-medium text-gray-700 dark:text-gray-300">{{ $programs->lastItem() }}</span>
            de
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $programs->total() }}</span>
            {{ Str::plural('programa', $programs->total()) }}
        </p>

        <div class="flex items-center gap-1">
            @if($programs->onFirstPage())
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
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-white bg-[#2563eb] select-none">{{ $p }}</span>
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

            @if($programs->hasMorePages())
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
