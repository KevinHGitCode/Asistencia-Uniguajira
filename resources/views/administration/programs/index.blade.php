<x-layouts.app :title="__('Programas')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="programsManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Programas'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="book-open" class="size-16 text-[#2563eb]" />
                <span>Programas</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $programs->count() }} {{ $programs->count() === 1 ? 'programa registrado' : 'programas registrados' }}
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('programs.download-template') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 text-sm font-medium transition-colors shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:icon.arrow-down-tray class="size-4" />
                Plantilla
            </a>
            <label for="import-trigger"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-medium transition-colors shadow-sm hover:bg-emerald-100 dark:hover:bg-emerald-900/50 cursor-pointer">
                <flux:icon.arrow-up-tray class="size-4" />
                Importar Excel
            </label>
            <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#2563eb] text-white text-sm font-medium transition-colors shadow-sm cursor-pointer hover:bg-[#1d4ed8]">
                <flux:icon.plus class="size-4" />
                Nuevo Programa
            </button>
        </div>
    </div>

    {{-- Formulario de importación oculto --}}
    <form id="import-form" action="{{ route('programs.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        <input id="import-trigger" type="file" name="excel_file" accept=".xlsx,.xls,.csv"
            onchange="document.getElementById('import-form').submit()">
    </form>

    @if($errors->has('excel_file'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ $errors->first('excel_file') }}
        </div>
    @endif

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
    @if(session('error') || $errors->has('name') || $errors->has('program_type'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') ?? $errors->first('name') ?? $errors->first('program_type') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        {{-- Header tabla --}}
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Programas</h2>
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" x-model="search" placeholder="Buscar..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2563eb] transition w-40 sm:w-56" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Programa</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium hidden sm:table-cell">Tipo</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Creado</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($programs as $program)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($program->name) }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <flux:icon.book-open class="size-5 text-[#2563eb]" />
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $program->name }}</span>
                                </div>
                            </td>
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
                                        @click="openEdit({{ $program->id }}, '{{ addslashes($program->name) }}', '{{ addslashes($program->program_type ?? '') }}')"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                        title="Editar">
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>

                                    @if(($program->participants_count ?? 0) === 0)
                                        <button
                                            @click="openDelete({{ $program->id }}, '{{ addslashes($program->name) }}')"
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
                                    <p class="text-sm">No hay programas registrados aún.</p>
                                    <button @click="openCreate()"
                                        class="text-sm text-[#2563eb] hover:underline cursor-pointer">
                                        Crear el primer programa
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: CREAR / EDITAR --}}
    <x-programs.form-modal />

    {{-- MODAL: ELIMINAR --}}
    <x-programs.delete-modal />
</div>

</x-layouts.app>
