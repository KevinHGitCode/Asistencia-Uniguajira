<x-layouts.app :title="__('Áreas')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6" x-data="areasManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('administracion.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    Administración
                </a>
                <flux:icon.chevron-right class="size-3" />
                <span class="text-gray-700 dark:text-gray-200">Áreas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                🗂️ Áreas
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $areas->count() }} {{ Str::plural('área', $areas->count()) }} registrada{{ $areas->count() !== 1 ? 's' : '' }}
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto">
            <flux:icon.plus class="size-4" />
            Nueva Área
        </button>
    </div>

    {{-- Alertas de sesión --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->has('name'))
        <div class="mb-3 px-4 py-3 rounded-lg bg-red-100 border border-red-300 text-red-700 text-sm">
            {{ $errors->first('name') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        {{-- Header tabla --}}
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Áreas</h2>
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" x-model="search" placeholder="Buscar..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition w-40 sm:w-56" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Nombre</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium hidden sm:table-cell">Dependencia</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Eventos</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Creada</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($areas as $area)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($area->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($area->dependency->name ?? '') }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                        <flux:icon.squares-2x2 class="size-4 text-emerald-500 dark:text-emerald-400" />
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $area->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                @if($area->dependency)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        <flux:icon.building-office-2 class="size-3" />
                                        {{ $area->dependency->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-zinc-500 text-xs">Sin dependencia</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                    {{ $area->events_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                                {{ $area->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit({{ $area->id }}, '{{ addslashes($area->name) }}', {{ $area->dependency_id ?? 'null' }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400 transition-colors"
                                        title="Editar">
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>
                                    <button
                                        @click="openDelete({{ $area->id }}, '{{ addslashes($area->name) }}')"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors"
                                        title="Eliminar">
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon.squares-2x2 class="size-12 opacity-30" />
                                    <p class="text-sm">No hay áreas registradas aún.</p>
                                    <button @click="openCreate()"
                                        class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Crear la primera área
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ======================== MODAL: CREAR / EDITAR ======================== --}}
    <x-areas.form-modal :dependencies="$dependencies" />


    {{-- ======================== MODAL: ELIMINAR ======================== --}}
    <x-areas.delete-modal />

</div>

{{-- @vite('resources/js/administration/areas/areas-manager.js') --}}

</x-layouts.app>