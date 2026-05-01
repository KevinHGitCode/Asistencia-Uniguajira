<x-layouts.app :title="__('Formatos')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6" x-data="formatsManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Formatos'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="document-text" class="size-16 text-[#e2a542]" />
                <span>Formatos</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $formats->count() }} {{ Str::plural('formato', $formats->count()) }} registrado{{ $formats->count() !== 1 ? 's' : '' }}
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#e2a542] text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto cursor-pointer">
            <flux:icon.plus class="size-4" />
            Nuevo Formato
        </button>
    </div>

    {{-- Alertas --}}
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

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div x-data="{ show: true }" x-show="show"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0 mt-0.5" />
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors cursor-pointer">
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Formatos</h2>
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" x-model="search" placeholder="Buscar..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition w-40 sm:w-56" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Nombre</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Identificador</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Dependencias</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Creado</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($formats as $format)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($format->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($format->slug) }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0">
                                        <flux:icon name="document-text" class="size-6 text-[#e2a542]" />
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $format->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <code class="px-2 py-0.5 rounded bg-gray-100 dark:bg-zinc-800 text-xs text-gray-600 dark:text-gray-400">{{ $format->slug }}</code>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center">
                                @php
                                    $depNames = $format->dependencies->pluck('name')->values();
                                    $primaryDep = $depNames->first();
                                    $extraDepsCount = max(0, $depNames->count() - 1);
                                @endphp

                                @if($depNames->isNotEmpty())
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#e2a542] max-w-[14rem] truncate" title="{{ $primaryDep }}">
                                            {{ Str::limit($primaryDep, 20) }}
                                        </span>
                                        @if($extraDepsCount > 0)
                                            <div class="relative group/deps">
                                                <button type="button"
                                                    class="inline-flex items-center rounded-full bg-[#e2a542] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#e2a542]/40">
                                                    +{{ $extraDepsCount }}
                                                </button>
                                                <div class="pointer-events-none absolute right-0 top-full z-20 mt-2 hidden min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200 group-hover/deps:block group-focus-within/deps:block">
                                                    <p class="mb-2 font-semibold">Dependencias asignadas</p>
                                                    <ul class="space-y-1">
                                                        @foreach($depNames as $depName)
                                                            <li class="truncate" title="{{ $depName }}">{{ $depName }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                                {{ $format->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit({{ $format->id }}, {{ Js::from($format->name) }}, {{ Js::from($format->slug) }}, {{ json_encode($format->dependencies->pluck('id')) }}, {{ Js::from($format->file) }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                        title="Editar">
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>
                                    <button
                                        @click="openDelete({{ $format->id }}, {{ Js::from($format->name) }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                        title="Eliminar">
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                    <a href="{{ route('formats.mapper', $format) }}"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 transition-colors cursor-pointer"
                                    title="Mapear coordenadas">
                                        <flux:icon name="map-pin" class="size-4" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon name="document-text" class="size-12 opacity-30" />
                                    <p class="text-sm">No hay formatos registrados aún.</p>
                                    <button @click="openCreate()"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                        Crear el primer formato
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modales --}}
    <x-formats.form-modal :dependencies="$dependencies" />
    <x-formats.delete-modal />

</div>

</x-layouts.app>