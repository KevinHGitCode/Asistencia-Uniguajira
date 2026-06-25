<x-layouts.app :title="__('Estamentos')">

<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12"
     x-data="{ ...participantTypesManager(), activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ session('active_tab', 'list') }}', setTab(tab) { this.activeTab = tab; const url = new URL(window.location); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url); } }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Estamentos'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="identification" class="size-16 text-[#0d9488]" />
                <span>Estamentos</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $totalParticipantTypes }} {{ $totalParticipantTypes === 1 ? 'estamento registrado' : 'estamentos registrados' }}
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#0d9488] text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto cursor-pointer hover:bg-[#0f766e]">
            <flux:icon.plus class="size-4" />
            Nuevo Estamento
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

    <x-administration.info-note color="#0d9488">
        Los estamentos son los <strong>tipos de participante</strong> válidos en el sistema
        (Estudiante, Docente, Administrativo, etc.). El Excel de importación usa la columna
        <code class="rounded px-1 font-mono">Tipo de Estamento</code>
        para clasificar cada fila — si el valor no coincide con un estamento registrado aquí,
        la fila será omitida. Un participante puede pertenecer a varios estamentos.
    </x-administration.info-note>

    {{-- Tabs --}}
    <div class="border-b border-neutral-200 dark:border-zinc-700">
        <nav class="flex gap-1">
            <button
                @click="setTab('list')"
                :class="activeTab === 'list'
                    ? 'border-b-2 border-[#0d9488] text-[#0d9488] dark:text-[#2dd4bf]'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.list-bullet class="size-4" />
                Listado
            </button>
            <button
                @click="setTab('import')"
                :class="activeTab === 'import'
                    ? 'border-b-2 border-[#0d9488] text-[#0d9488] dark:text-[#2dd4bf]'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.arrow-up-tray class="size-4" />
                Importar / Exportar
            </button>
        </nav>
    </div>

    {{-- TAB: LISTADO --}}
    <div x-show="activeTab === 'list'" x-transition>

        <livewire:administration.participant-type-table />
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
                <a href="{{ route('participant-types.download-template') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-medium transition-colors shrink-0">
                    <flux:icon.arrow-down-tray class="size-3.5 text-[#0d9488]" />
                    Descargar plantilla
                </a>
            </div>

            <div class="px-4 sm:px-6 py-6 flex flex-col gap-6">

                {{-- Info box sobre estamentos en el contexto de importación --}}
                <x-administration.info-note color="#0d9488">
                        Los estamentos son los <strong>tipos de participante</strong> válidos en el sistema.
                        El Excel de importación de participantes usa la columna
                        <code class="rounded px-1 font-mono">Tipo de Estamento</code>
                        para clasificar cada fila — si el valor no coincide con un estamento registrado aquí,
                        la fila será omitida.
                </x-administration.info-note>

                {{-- Error de importación --}}
                @error('excel_file')
                    <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                        <flux:icon.x-circle class="size-5 shrink-0 mt-0.5" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- Drop zone --}}
                <form action="{{ route('participant-types.import') }}" method="POST" enctype="multipart/form-data"
                      x-data="{ fileName: '', dragging: false }"
                      class="flex flex-col gap-4">
                    @csrf

                    <div
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; const f = $event.dataTransfer.files[0]; if (f) { fileName = f.name; $refs.fileInput.files = $event.dataTransfer.files; }"
                        :class="dragging ? 'border-[#0d9488] bg-teal-50 dark:bg-teal-900/20' : 'border-neutral-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50'"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-8 transition-colors text-center cursor-pointer">

                        <flux:icon.document-arrow-up class="size-10 text-gray-400 dark:text-zinc-500" />
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-show="!fileName">Arrastra tu archivo aquí o <span class="text-[#0d9488]">selecciona uno</span></span>
                                <span x-show="fileName" class="text-[#0d9488]" x-text="fileName"></span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">.xlsx, .xls, .csv · Máximo 10 MB</p>
                        </div>
                        <input x-ref="fileInput" type="file" name="excel_file" accept=".xlsx,.xls,.csv"
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            @change="fileName = $event.target.files[0]?.name ?? ''" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#0d9488] hover:opacity-90 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
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
                    <a href="{{ route('participant-types.download-export') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-sm font-medium transition-colors shadow-sm shrink-0">
                        <flux:icon.arrow-down-tray class="size-4 text-[#0d9488]" />
                        Descargar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: CREAR / EDITAR --}}
    <x-participant-types.form-modal />

    {{-- MODAL: ELIMINAR --}}
    <x-participant-types.delete-modal />
</div>

</x-layouts.app>
