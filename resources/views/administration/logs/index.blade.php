<x-layouts.app :title="__('Registros de Actividad')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="{ showClearModal: false, showFilters: false }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Registros de Actividad'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="shield-check" class="size-16 text-[#64748b]" />
                <span>Registros de Actividad</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $logs->total() }} {{ Str::plural('registro', $logs->total()) }}
            </p>
        </div>
        @if($logs->total() > 0)
            <button @click="showClearModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer self-start sm:self-auto hover:opacity-90">
                <flux:icon.trash class="size-4" />
                Limpiar logs
            </button>
        @endif
    </div>

    {{-- Flash: success --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Filtros</h2>
            <button @click="showFilters = !showFilters" class="sm:hidden text-sm text-[#cc5e50] font-medium cursor-pointer">
                <span x-text="showFilters ? 'Ocultar' : 'Mostrar'"></span>
            </button>
        </div>

        <form method="GET" action="{{ route('activity-logs.index') }}"
              class="px-4 sm:px-6 py-4" :class="{ 'hidden sm:block': !showFilters }">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                {{-- Search --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Descripción, módulo, usuario..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                </div>

                {{-- Module --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Módulo</label>
                    <select name="module"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Todos los módulos</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Acción</label>
                    <select name="action"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Todas las acciones</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- User --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Usuario</label>
                    <select name="user_id"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date from --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                </div>

                {{-- Date to --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#64748b] hover:opacity-90 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
                    <flux:icon.magnifying-glass class="size-4" />
                    Filtrar
                </button>
                <a href="{{ route('activity-logs.index') }}"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    Limpiar filtros
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Registros</h2>

            @if($logs->hasPages())
            <div class="flex items-center gap-0.5 ml-auto">
                @if($logs->onFirstPage())
                    <span class="p-1.5 rounded-lg text-gray-300 dark:text-zinc-600 cursor-not-allowed">
                        <flux:icon.chevron-left class="size-4" />
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                       class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                       title="Página anterior">
                        <flux:icon.chevron-left class="size-4" />
                    </a>
                @endif

                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 px-1.5 tabular-nums select-none">
                    {{ $logs->currentPage() }}&thinsp;/&thinsp;{{ $logs->lastPage() }}
                </span>

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                       class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                       title="Página siguiente">
                        <flux:icon.chevron-right class="size-4" />
                    </a>
                @else
                    <span class="p-1.5 rounded-lg text-gray-300 dark:text-zinc-600 cursor-not-allowed">
                        <flux:icon.chevron-right class="size-4" />
                    </span>
                @endif
            </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Fecha</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Actor</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Acción</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Módulo</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Descripción</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium hidden lg:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                            {{-- Fecha --}}
                            <td class="px-4 sm:px-6 py-4 text-gray-600 dark:text-gray-300 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>

                            {{-- Actor --}}
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $log->user->name }}
                                    </span>
                                @elseif($log->participant)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        {{ $log->participant->first_name ? trim($log->participant->first_name . ' ' . $log->participant->last_name) : $log->participant->document }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400">
                                        Sistema
                                    </span>
                                @endif
                            </td>

                            {{-- Acción --}}
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                @php
                                    $actionColors = [
                                        'crear'                 => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                        'editar'                => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                        'eliminar'              => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        'importar'              => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'exportar'              => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'login'                 => 'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400',
                                        'logout'                => 'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400',
                                        'registrar_asistencia'  => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                                        'confirmar_asistencia'  => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                                        'terminar_evento'       => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                    ];
                                    $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>

                            {{-- Módulo --}}
                            <td class="px-4 sm:px-6 py-4 text-gray-700 dark:text-gray-300 text-sm">
                                {{ ucfirst($log->module) }}
                            </td>

                            {{-- Descripción --}}
                            <td class="px-4 sm:px-6 py-4 text-gray-700 dark:text-gray-300 text-sm max-w-xs">
                                <div class="break-words whitespace-normal">{{ $log->description }}</div>
                                @if(!empty($log->metadata))
                                    <details class="mt-1">
                                        <summary class="text-xs text-gray-400 dark:text-zinc-500 cursor-pointer hover:text-gray-600 dark:hover:text-zinc-300">Ver cambios</summary>
                                        <div class="mt-1 space-y-0.5 text-xs">
                                            @foreach($log->metadata as $field => $detail)
                                                @if(is_array($detail) && isset($detail['old'], $detail['new']))
                                                    <div class="flex flex-wrap gap-1">
                                                        <span class="font-medium text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                        <span class="line-through text-red-400 dark:text-red-500">{{ Str::limit($detail['old'], 40) }}</span>
                                                        <span class="text-gray-400">&rarr;</span>
                                                        <span class="text-emerald-600 dark:text-emerald-400">{{ Str::limit($detail['new'], 40) }}</span>
                                                    </div>
                                                @else
                                                    <div>
                                                        <span class="font-medium text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                        <span>{{ is_array($detail) ? json_encode($detail) : $detail }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </td>

                            {{-- IP --}}
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 text-xs hidden lg:table-cell">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon.shield-check class="size-12 opacity-30" />
                                    <p class="text-sm">No hay registros de actividad{{ request()->hasAny(['search', 'module', 'action', 'user_id', 'date_from', 'date_to']) ? ' con los filtros aplicados' : ' aún' }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($logs->hasPages())
            @php
                $current = $logs->currentPage();
                $last = $logs->lastPage();
                $left = max(1, $current - 2);
                $right = min($last, $current + 2);
            @endphp
            <div class="px-4 sm:px-6 py-4 border-t border-neutral-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Mostrando
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $logs->firstItem() }}</span>–<span class="font-medium text-gray-700 dark:text-gray-300">{{ $logs->lastItem() }}</span>
                    de
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $logs->total() }}</span>
                    {{ Str::plural('registro', $logs->total()) }}
                </p>

                <div class="flex items-center gap-1">
                    @if($logs->onFirstPage())
                        <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 dark:text-zinc-600 cursor-not-allowed select-none">Anterior</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}"
                           class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                            Anterior
                        </a>
                    @endif

                    @if($left > 1)
                        <a href="{{ $logs->url(1) }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">1</a>
                        @if($left > 2)
                            <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                        @endif
                    @endif

                    @for($p = $left; $p <= $right; $p++)
                        @if($p === $current)
                            <span class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold text-white bg-[#64748b] select-none">{{ $p }}</span>
                        @else
                            <a href="{{ $logs->url($p) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $p }}</a>
                        @endif
                    @endfor

                    @if($right < $last)
                        @if($right < $last - 1)
                            <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 dark:text-zinc-500 select-none">…</span>
                        @endif
                        <a href="{{ $logs->url($last) }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">{{ $last }}</a>
                    @endif

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}"
                           class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                            Siguiente
                        </a>
                    @else
                        <span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 dark:text-zinc-600 cursor-not-allowed select-none">Siguiente</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de confirmación para limpiar --}}
    <div x-show="showClearModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/50" @click="showClearModal = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl max-w-md w-full p-6 z-10"
             @click.away="showClearModal = false">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Limpiar registros antiguos</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Se eliminarán todos los registros de actividad con más de <strong>90 días</strong> de antigüedad. Esta acción no se puede deshacer.
            </p>
            <div class="flex items-center justify-end gap-3">
                <button @click="showClearModal = false"
                    class="px-4 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <form method="POST" action="{{ route('activity-logs.clear') }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:opacity-90 transition-colors shadow-sm cursor-pointer">
                        Sí, limpiar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
