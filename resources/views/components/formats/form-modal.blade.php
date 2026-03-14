@props(['dependencies'])

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
         class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                x-text="editingId ? 'Editar Formato' : 'Nuevo Formato'"></h3>
            <button @click="closeForm()"
                class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>

        <form
            :action="editingId
                ? '{{ route('formats.update', '__id__') }}'.replace('__id__', editingId)
                : '{{ route('formats.store') }}'"
            method="POST"
            class="px-6 py-5 flex flex-col gap-4">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    x-model="formName"
                    required
                    placeholder="Ej: Formato Bienestar"
                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Identificador (slug) <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="slug"
                    x-model="formSlug"
                    required
                    placeholder="Ej: bienestar"
                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                <p class="text-xs text-gray-400">Debe coincidir con la clave en el archivo de configuración.</p>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Dependencias asignadas
                </label>
                <div class="max-h-40 overflow-y-auto rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2 space-y-1">
                    @foreach($dependencies as $dependency)
                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                name="dependencies[]"
                                value="{{ $dependency->id }}"
                                :checked="selectedDependencies.includes({{ $dependency->id }})"
                                @change="toggleDependency({{ $dependency->id }})"
                                class="rounded border-gray-300 text-[#e2a542] focus:ring-[#7c6db0]" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $dependency->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400">Selecciona las dependencias que pueden usar este formato.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="closeForm()"
                    class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-[#e2a542] hover:bg-[#6b5c9e] text-white font-medium transition-colors shadow-sm cursor-pointer"
                    x-text="editingId ? 'Guardar cambios' : 'Crear formato'">
                </button>
            </div>
        </form>
    </div>
</div>