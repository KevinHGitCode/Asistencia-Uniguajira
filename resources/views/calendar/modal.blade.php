<!-- Modal mejorado -->
<div id="calendarModal"
    class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm transition flex items-center justify-center">
    <div
        class="modal-content bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col animate-fadeIn overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between p-6 bg-gray-50/80 dark:bg-neutral-800/40">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg">
                    <flux:icon.calendar-check class="size-8" />
                </div>
                <div>
                    <h2 id="calendarModalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Eventos</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Detalles de los eventos programados</p>
                </div>
            </div>

            <button type="button"
                class="p-2 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-full transition-colors"
                onclick="closeModal()">X
            </button>
        </div>

        <!-- Body -->
        <div id="calendarModalBody" class="flex-1 p-6 overflow-y-auto scrollbar-thin">
            <div class="flex items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent"></div>
                    <span>Cargando eventos...</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center p-6 bg-gray-50/80 dark:bg-neutral-800/40">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                <span id="eventCount">0 eventos</span>
            </div>
            <button type="button"
                class="px-5 py-2 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors font-medium shadow-md"
                onclick="closeModal()">
                Cerrar
            </button>
        </div>
    </div>
</div>
