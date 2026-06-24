<x-layouts.app :title="__('Sedes')">
<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12"
     x-data="{ ...campusesManager(), activeTab: new URLSearchParams(window.location.search).get('tab') || '{{ session('active_tab', 'list') }}', setTab(tab) { this.activeTab = tab; const url = new URL(window.location); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url); } }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <x-breadcrumb class="mb-1" :items="[['label' => 'Administración', 'route' => 'administracion.index'], ['label' => 'Sedes']]" />
            <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl"><flux:icon name="map-pin" class="size-16 text-blue-600 dark:text-blue-300" /><span>Sedes</span></h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $totalCampuses }} {{ $totalCampuses === 1 ? 'sede registrada' : 'sedes registradas' }} en el sistema.</p>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 self-start rounded-lg bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-600 shadow-sm transition-colors hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 sm:self-auto"><flux:icon.plus class="size-4" />Nueva Sede</button>
    </div>

    <x-administration.info-note color="#2563eb">
        Las <strong>sedes</strong> organizan la información institucional por ubicación. Al crear dependencias y programas, la sede define dónde quedarán disponibles; su nombre también se usa para reconocer el sufijo <code class="rounded px-1 font-mono">- Sede</code> durante las importaciones.
    </x-administration.info-note>

    @if(session('success'))<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400"><flux:icon.check-circle class="size-5 shrink-0" />{{ session('success') }}</div>@endif
    @if(session('error') || $errors->any())<div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"><flux:icon.x-circle class="size-5 shrink-0" />{{ session('error') ?: $errors->first() }}</div>@endif

    <div class="border-b border-neutral-200 dark:border-zinc-700">
        <nav class="flex gap-1">
            <button
                @click="setTab('list')"
                :class="activeTab === 'list'
                    ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-300'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.list-bullet class="size-4" />
                Listado
            </button>
            <button
                @click="setTab('import')"
                :class="activeTab === 'import'
                    ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-300'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.arrow-up-tray class="size-4" />
                Importar / Exportar
            </button>
        </nav>
    </div>

    <div x-show="activeTab === 'list'" x-transition><livewire:administration.campus-table /></div>
    <div x-show="activeTab === 'import'" x-transition>
        <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4 border-b border-neutral-200 bg-zinc-50 px-4 py-4 dark:border-zinc-700 dark:bg-zinc-900 sm:px-6"><div><h2 class="text-base font-semibold text-gray-900 dark:text-white">Importar desde Excel</h2><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Columna requerida: <code class="font-mono">Nombre</code>. Los duplicados se omiten automáticamente.</p></div><a href="{{ route('campuses.download-template') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300"><flux:icon.arrow-down-tray class="size-3.5 text-blue-600 dark:text-blue-300" />Descargar plantilla</a></div>
            <div class="flex flex-col gap-6 px-4 py-6 sm:px-6"><form action="{{ route('campuses.import') }}" method="POST" enctype="multipart/form-data" x-data="{ fileName: '', dragging: false }" class="flex flex-col gap-4">@csrf
                <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; const f = $event.dataTransfer.files[0]; if (f) { fileName = f.name; $refs.fileInput.files = $event.dataTransfer.files; }" :class="dragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40' : 'border-neutral-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50'" class="relative flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-8 text-center transition-colors"><flux:icon.document-arrow-up class="size-10 text-gray-400" /><div><p class="text-sm font-medium text-gray-700 dark:text-gray-300"><span x-show="!fileName">Arrastra tu archivo aquí o <span class="text-blue-600 dark:text-blue-300">selecciona uno</span></span><span x-show="fileName" x-text="fileName" class="text-blue-600 dark:text-blue-300"></span></p><p class="mt-1 text-xs text-gray-400">.xlsx, .xls, .csv · Máximo 10 MB</p></div><input x-ref="fileInput" type="file" name="excel_file" accept=".xlsx,.xls,.csv" class="absolute inset-0 cursor-pointer opacity-0" @change="fileName = $event.target.files[0]?.name ?? ''" /></div>
                <div class="flex justify-end"><button class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-5 py-2.5 text-sm font-medium text-blue-600 shadow-sm transition-colors hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50"><flux:icon.arrow-up-tray class="size-4" />Importar</button></div></form>
                <div class="border-t border-neutral-200 dark:border-zinc-700"></div><div class="flex items-center justify-between gap-4"><div><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Exportar datos actuales</h3><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Descarga el listado completo como archivo Excel.</p></div><a href="{{ route('campuses.download-export') }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-neutral-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-300"><flux:icon.arrow-down-tray class="size-4 text-blue-600 dark:text-blue-300" />Descargar Excel</a></div></div>
        </div>
    </div>

    <div x-show="showForm" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none"><div class="absolute inset-0 bg-black/50" @click="closeForm()"></div><div class="relative z-10 w-full max-w-md rounded-2xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-700"><h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Editar Sede' : 'Nueva Sede'"></h3><button @click="closeForm()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100"><flux:icon.x-mark class="size-5" /></button></div><form :action="editingId ? '{{ route('campuses.update', '__id__') }}'.replace('__id__', editingId) : '{{ route('campuses.store') }}'" method="POST" class="flex flex-col gap-4 px-6 py-5">@csrf <div class="flex flex-col gap-1.5"><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label><input name="name" x-model="formName" required maxlength="100" placeholder="Ej: Riohacha" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" /><p class="text-xs text-gray-400">Este nombre se usa para asignar sedes durante las importaciones.</p></div><div class="flex justify-end gap-3 pt-2"><button type="button" @click="closeForm()" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm text-gray-700 dark:border-zinc-700 dark:text-gray-300">Cancelar</button><button class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-600 shadow-sm hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50" x-text="editingId ? 'Guardar cambios' : 'Crear sede'"></button></div></form></div></div>
    <div x-show="showDelete" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none"><div class="absolute inset-0 bg-black/50" @click="closeDelete()"></div><div class="relative z-10 flex w-full max-w-sm flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900"><div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/30"><flux:icon.exclamation-triangle class="size-6 text-red-500" /></div><div class="text-center"><h3 class="text-base font-semibold text-gray-900 dark:text-white">¿Eliminar sede?</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Se eliminará <strong x-text="`&quot;${deleteName}&quot;`"></strong>. No será posible si tiene registros asociados.</p></div><form :action="'{{ route('campuses.destroy', '__id__') }}'.replace('__id__', deleteId)" method="POST" class="flex gap-3">@csrf @method('DELETE')<button type="button" @click="closeDelete()" class="flex-1 rounded-lg border border-neutral-200 px-4 py-2 text-sm dark:border-zinc-700">Cancelar</button><button class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Sí, eliminar</button></form></div></div>
</div></x-layouts.app>
