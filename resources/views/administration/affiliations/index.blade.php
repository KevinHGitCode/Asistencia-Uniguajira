<x-layouts.app :title="__('Afiliaciones')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="{ ...affiliationsManager(), activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ session('active_tab', 'list') }}', setTab(tab) { this.activeTab = tab; const url = new URL(window.location); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url); } }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Afiliaciones'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="tag" class="size-16 text-[#7c6fcd]" />
                <span>Afiliaciones</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $affiliations->count() }} {{ $affiliations->count() === 1 ? 'afiliación registrada' : 'afiliaciones registradas' }}
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#7c6fcd] text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto cursor-pointer hover:bg-[#6b5eb8]">
            <flux:icon.plus class="size-4" />
            Nueva Afiliación
        </button>
    </div>

    {{-- Flash: success --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- Flash: error --}}
    @if(session('error') || $errors->has('name') || $errors->has('excel_file'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') ?? $errors->first('name') ?? $errors->first('excel_file') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-neutral-200 dark:border-zinc-700">
        <nav class="flex gap-1">
            <button
                @click="setTab('list')"
                :class="activeTab === 'list'
                    ? 'border-b-2 border-[#7c6fcd] text-[#7c6fcd] dark:text-[#a89fdf]'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.list-bullet class="size-4" />
                Listado
            </button>
            <button
                @click="setTab('import')"
                :class="activeTab === 'import'
                    ? 'border-b-2 border-[#7c6fcd] text-[#7c6fcd] dark:text-[#a89fdf]'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.arrow-up-tray class="size-4" />
                Importar / Exportar
            </button>
        </nav>
    </div>

    {{-- TAB: LISTADO --}}
    <div x-show="activeTab === 'list'" x-transition>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

            {{-- Header tabla --}}
            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Afiliaciones</h2>
                <div class="relative">
                    <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input type="text" x-model="search" placeholder="Buscar..."
                        class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#7c6fcd] transition w-40 sm:w-56" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                            <th class="px-4 sm:px-6 py-3 text-left font-medium">Nombre</th>
                            <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                            <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Creada</th>
                            <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                        @forelse($affiliations as $affiliation)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                x-show="search === '' || '{{ strtolower($affiliation->name) }}'.includes(search.toLowerCase())"
                                x-transition>
                                <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.tag class="size-5 text-[#7c6fcd]" />
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $affiliation->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#7c6fcd]">
                                        {{ $affiliation->participants_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs hidden sm:table-cell">
                                    {{ $affiliation->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEdit({{ $affiliation->id }}, '{{ addslashes($affiliation->name) }}')"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                            title="Editar">
                                            <flux:icon.pencil-square class="size-4" />
                                        </button>

                                        @if(($affiliation->participants_count ?? 0) === 0)
                                            <button
                                                @click="openDelete({{ $affiliation->id }}, '{{ addslashes($affiliation->name) }}')"
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
                                        <flux:icon.tag class="size-12 opacity-30" />
                                        <p class="text-sm">No hay afiliaciones registradas aún.</p>
                                        <button @click="openCreate()"
                                            class="text-sm text-[#7c6fcd] hover:underline cursor-pointer">
                                            Crear la primera afiliación
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB: IMPORTAR / EXPORTAR --}}
    <div x-show="activeTab === 'import'" x-transition>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

            {{-- Import section header --}}
            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Importar desde Excel</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Columna requerida: <code class="font-mono">Nombre</code>. Los duplicados se omiten automáticamente.
                    </p>
                </div>
                <a href="{{ route('affiliations.download-template') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-medium transition-colors shrink-0">
                    <flux:icon.arrow-down-tray class="size-3.5 text-[#7c6fcd]" />
                    Descargar plantilla
                </a>
            </div>

            <div class="px-4 sm:px-6 py-6 flex flex-col gap-6">

                {{-- Error de importación --}}
                @error('excel_file')
                    <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                        <flux:icon.x-circle class="size-5 shrink-0 mt-0.5" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- Drop zone --}}
                <form action="{{ route('affiliations.import') }}" method="POST" enctype="multipart/form-data"
                      x-data="{ fileName: '', dragging: false }"
                      class="flex flex-col gap-4">
                    @csrf

                    <div
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; const f = $event.dataTransfer.files[0]; if (f) { fileName = f.name; $refs.fileInput.files = $event.dataTransfer.files; }"
                        :class="dragging ? 'border-[#7c6fcd] bg-purple-50 dark:bg-purple-900/20' : 'border-neutral-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50'"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-8 transition-colors text-center cursor-pointer">

                        <flux:icon.document-arrow-up class="size-10 text-gray-400 dark:text-zinc-500" />
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-show="!fileName">Arrastra tu archivo aquí o <span class="text-[#7c6fcd]">selecciona uno</span></span>
                                <span x-show="fileName" class="text-[#7c6fcd]" x-text="fileName"></span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">.xlsx, .xls, .csv · Máximo 10 MB</p>
                        </div>
                        <input x-ref="fileInput" type="file" name="excel_file" accept=".xlsx,.xls,.csv"
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            @change="fileName = $event.target.files[0]?.name ?? ''" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#7c6fcd] hover:opacity-90 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
                            <flux:icon.arrow-up-tray class="size-4" />
                            Importar
                        </button>
                    </div>
                </form>

                {{-- Divider --}}
                <div class="border-t border-neutral-200 dark:border-zinc-700"></div>

                {{-- Export section --}}
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Exportar datos actuales</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Descarga el listado completo como archivo Excel.
                        </p>
                    </div>
                    <a href="{{ route('affiliations.download-export') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors shadow-sm shrink-0">
                        <flux:icon.arrow-down-tray class="size-4 text-[#7c6fcd]" />
                        Descargar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: CREAR / EDITAR --}}
    <x-affiliations.form-modal />

    {{-- MODAL: ELIMINAR --}}
    <x-affiliations.delete-modal />
</div>

</x-layouts.app>
