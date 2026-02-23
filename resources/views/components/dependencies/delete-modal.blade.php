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
             class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10 p-6 flex flex-col gap-4">

            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/30 mx-auto">
                <flux:icon.exclamation-triangle class="size-6 text-red-500" />
            </div>

            <div class="text-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">¿Eliminar dependencia?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Estás por eliminar <strong class="text-gray-800 dark:text-gray-200" x-text="`&quot;${deleteName}&quot;`"></strong>.
                    Esta acción no se puede deshacer.
                </p>
            </div>

            <form
                :action="'{{ url('administracion/dependencies/delete') }}/' + deleteId"
                method="POST"
                class="flex gap-3">
                @csrf
                <button type="button" @click="closeDelete()"
                    class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors shadow-sm">
                    Sí, eliminar
                </button>
            </form>
        </div>
    </div>