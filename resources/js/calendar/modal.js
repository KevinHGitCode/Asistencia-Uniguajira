export function openModal(fecha, eventos) {
    const modal = document.getElementById('calendarModal');
    const modalTitle = document.getElementById('calendarModalTitle');
    const modalContent = document.getElementById('calendarModalBody');
    const eventCount = document.getElementById('eventCount');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    modalTitle.textContent = `Eventos para el ${fecha}`;

    if (eventos.length > 0) {
        modalContent.innerHTML = eventos.map(e => `
            <div class="p-4 mb-4 rounded-xl border border-gray-300 dark:border-neutral-700 
                        bg-gradient-to-br from-white to-gray-50 dark:from-neutral-800 dark:to-neutral-900 
                        shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1">
                
                <!-- Título del evento -->
                <h3 class="font-bold text-lg flex items-center gap-2 text-gray-800 dark:text-white">
                    ${e.title}
                </h3>
                
                <!-- Descripción -->
                <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    ${e.description ?? '<em class="text-gray-400">Sin descripción</em>'}
                </p>
                
                <!-- Hora -->
                <div class="mt-3 flex items-center text-sm text-gray-600 dark:text-gray-300">
                    🕗 ${e.start_time ?? 'No definido'}
                </div>

                <!-- Ubicación -->
                <div class="mt-2 flex items-center text-sm text-gray-600 dark:text-gray-300">
                    📌 ${e.location ?? 'Ubicación no definida'}
                </div>
            </div>
        `).join('');
    } else {
        modalContent.innerHTML = `<p>No hay eventos registrados</p>`;
    }

    eventCount.textContent = `${eventos.length} evento${eventos.length !== 1 ? 's' : ''}`;

    modal.classList.remove('hidden');
}


export function closeModal() {
    const modal = document.getElementById("calendarModal");
    modal.classList.remove('flex');
    modal.classList.add("hidden");
    
}




window.closeModal = closeModal;
