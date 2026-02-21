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
             class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editingId ? 'Editar Área' : 'Nueva Área'"></h3>
                <button @click="closeForm()"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <form
                :action="editingId
                    ? '{{ url('administracion/areas/edit') }}/' + editingId
                    : '{{ route('areas.store') }}'"
                method="POST"
                class="px-6 py-5 flex flex-col gap-4">
                @csrf

                {{-- Nombre --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        x-model="formName"
                        required
                        placeholder="Ej: Nómina"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                    @error('name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dependencia --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Dependencia <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="dependency_id"
                        x-model="formDependencyId"
                        required
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="" disabled>Selecciona una dependencia</option>
                        @foreach($dependencies as $dependency)
                            <option value="{{ $dependency->id }}">{{ $dependency->name }}</option>
                        @endforeach
                    </select>
                    @error('dependency_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="closeForm()"
                        class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors shadow-sm"
                        x-text="editingId ? 'Guardar cambios' : 'Crear área'">
                    </button>
                </div>
            </form>
        </div>
    </div>