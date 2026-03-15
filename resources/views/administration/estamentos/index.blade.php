<x-layouts.app :title="__('Estamentos')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="{
         search: '',
         showForm: false,
         formName: '',
         openCreate() { this.formName = ''; this.showForm = true; },
         closeForm()  { this.showForm = false; },
     }">

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
                {{ $estamentos->count() }} {{ $estamentos->count() === 1 ? 'estamento registrado' : 'estamentos registrados' }}
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
            la fila será omitida.
        </span>
    </div>

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        {{-- Header tabla --}}
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
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($estamentos as $estamento)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($estamento->name) }}'.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <flux:icon.identification class="size-5 text-[#0d9488]" />
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $estamento->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white bg-[#0d9488]">
                                    {{ $estamento->participants_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center text-gray-500 dark:text-gray-400 text-xs hidden sm:table-cell">
                                {{ $estamento->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center">
                                @if(($estamento->participants_count ?? 0) === 0)
                                    <form action="{{ route('estamentos.destroy', $estamento) }}" method="POST"
                                          x-data
                                          @submit.prevent="if(confirm('¿Eliminar el estamento «{{ $estamento->name }}»?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                            <flux:icon.trash class="size-3.5" />
                                            Eliminar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-zinc-600 italic">En uso</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon.identification class="size-12 opacity-30" />
                                    <p class="text-sm">No hay estamentos registrados aún.</p>
                                    <button @click="openCreate()"
                                        class="text-sm text-[#0d9488] hover:underline cursor-pointer">
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

    {{-- ======================== MODAL: CREAR ======================== --}}
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="closeForm()"></div>

        <div x-show="showForm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nuevo Estamento</h3>
                <button @click="closeForm()"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <form action="{{ route('estamentos.store') }}" method="POST" class="px-6 py-5 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        x-model="formName"
                        x-ref="nameInput"
                        x-init="$watch('showForm', v => v && $nextTick(() => $refs.nameInput.focus()))"
                        required
                        maxlength="100"
                        placeholder="Ej: Egresado, Contratista…"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#0d9488] transition" />
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Este nombre debe coincidir exactamente con el valor en la columna
                        <code class="font-mono bg-zinc-100 dark:bg-zinc-800 px-1 rounded">Tipo de Estamento</code>
                        del Excel.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="closeForm()"
                        class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-[#0d9488] hover:bg-[#0f766e] text-white font-medium transition-colors shadow-sm cursor-pointer">
                        Crear estamento
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

</x-layouts.app>
