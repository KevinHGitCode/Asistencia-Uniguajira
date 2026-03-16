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
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Documento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Estamento(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider hidden md:table-cell">Programa(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider hidden lg:table-cell">Vinculación</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider hidden lg:table-cell">Correo</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse ($participants as $participant)
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
                        <td class="px-4 py-3">
                            @if($participant->types->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($participant->types as $type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                            {{ $type->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @elseif($participant->role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $participant->role }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($participant->programs->isNotEmpty())
                                <div class="space-y-0.5">
                                    @foreach($participant->programs->take(2) as $prog)
                                        <p class="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[200px]">
                                            {{ $prog->name }}{{ $prog->campus ? ' – ' . $prog->campus : '' }}
                                        </p>
                                    @endforeach
                                    @if($participant->programs->count() > 2)
                                        <p class="text-xs text-gray-400 dark:text-zinc-500">+{{ $participant->programs->count() - 2 }} más</p>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-600 dark:text-zinc-400">
                            {{ $participant->affiliations->first()?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500 dark:text-zinc-400 truncate max-w-[160px]">
                            {{ $participant->email ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
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
