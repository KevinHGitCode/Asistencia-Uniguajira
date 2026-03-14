<div x-show="showDelete"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="closeDelete()"></div>

    <div x-show="showDelete"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

        <div class="px-6 py-5 flex flex-col items-center gap-4 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400" />
            </div>

            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Eliminar formato</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    ¿Estás seguro de eliminar <strong x-text="deleteName" class="text-gray-900 dark:text-white"></strong>?
                    Se desvinculará de todas las dependencias.
                </p>
            </div>

            <div class="flex items-center gap-3 w-full pt-2">
                <button @click="closeDelete()"
                    class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <form
                    :action="'{{ route('formats.destroy', '__id__') }}'.replace('__id__', deleteId)"
                    method="POST"
                    class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors cursor-pointer">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>