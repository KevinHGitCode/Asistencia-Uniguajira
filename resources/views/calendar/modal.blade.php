<!-- Modal mejorado -->
<div id="calendarModal"
    class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm transition items-center justify-center hidden">
    <div
        class="modal-content bg-zinc-50 dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col animate-fadeIn overflow-hidden">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:p-6 bg-zinc-50 dark:bg-zinc-900"
            x-data="{ canCreate: false }"
            @calendar-modal-opened.window="
                const selected = new Date($event.detail.date + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                canCreate = selected >= today;
            ">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 rounded-lg shrink-0">
                    <flux:icon.calendar-check class="size-6 sm:size-8" />
                </div>
                <div class="min-w-0">
                    <h2 id="calendarModalTitle" class="text-base sm:text-xl font-bold text-gray-900 dark:text-white truncate">Eventos</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Detalles de los eventos programados</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                {{-- Botón nuevo evento: solo visible si fecha >= hoy --}}
                <div x-show="canCreate" x-cloak>
                    <flux:modal.trigger name="create-event-modal">
                        <flux:button 
                            variant="primary" 
                            size="sm" 
                            class="hover:scale-105 transition-transform cursor-pointer"
                            x-on:click="
                                if (window.selectedCalendarDate) {
                                    Livewire.dispatch('set-event-date', { date: window.selectedCalendarDate });
                                }
                            ">
                            <svg class="size-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Nuevo evento') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                {{-- Botón cerrar --}}
                <button type="button"
                    class="p-2 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-full transition-colors cursor-pointer"
                    onclick="closeModal()">X
                </button>
            </div>
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
        <div class="flex justify-between items-center p-6 bg-zinc-50 dark:bg-zinc-900">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                <span id="eventCount">0 eventos</span>
            </div>
            <button type="button"
                class="px-5 py-2 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors font-medium shadow-md cursor-pointer"
                onclick="closeModal()">
                Cerrar
            </button>
        </div>
    </div>
</div>
