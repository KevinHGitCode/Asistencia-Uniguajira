<x-layouts.app :title="__('Estamentos')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="participantTypesManager()">

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
                {{ $participantTypes->count() }} {{ $participantTypes->count() === 1 ? 'estamento registrado' : 'estamentos registrados' }}
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
    @if(session('error') || $errors->has('name'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') ?? $errors->first('name') }}
        </div>
    @endif

    {{-- Info box --}}
    <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-400 text-sm">
        <flux:icon.information-circle class="size-5 shrink-0 mt-0.5" />
        <span>
            Los estamentos son los <strong>tipos de participante</strong> válidos en el sistema
            (Estudiante, Docente, Administrativo, etc.). El Excel de importación usa la columna
            <code class="font-mono bg-teal-100 dark:bg-teal-900 px-1 rounded">Tipo de Estamento</code>
            para clasificar cada fila — si el valor no coincide con un estamento registrado aquí,
            la fila será omitida. Un participante puede pertenecer a varios estamentos.
        </span>
    </div>

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Estamentos</h2>
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" x-model="search" placeholder="Buscar..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0d9488] transition w-40 sm:w-56" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Nombre</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Participantes</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium hidden sm:table-cell">Creado</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($participantTypes as $type)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($type->name) }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <flux:icon.identification class="size-5 text-[#0d9488]" />
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $type->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#0d9488]">
                                    {{ $type->participants_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs hidden sm:table-cell">
                                {{ $type->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit({{ $type->id }}, '{{ addslashes($type->name) }}')"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                        title="Editar">
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>

                                    @if(($type->participants_count ?? 0) === 0)
                                        <button
                                            @click="openDelete({{ $type->id }}, '{{ addslashes($type->name) }}')"
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
                                    <flux:icon.identification class="size-12 opacity-30" />
                                    <p class="text-sm">No hay estamentos registrados aún.</p>
                                    <button @click="openCreate()" class="text-sm text-[#0d9488] hover:underline cursor-pointer">
                                        Crear el primer estamento
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
    <x-participant-types.form-modal />

    {{-- MODAL: ELIMINAR --}}
    <x-participant-types.delete-modal />
</div>

</x-layouts.app>


