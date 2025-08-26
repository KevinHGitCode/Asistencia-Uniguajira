export function openModal(fecha, eventos) {
    const modal = document.getElementById('calendarModal');
    const modalTitle = document.getElementById('calendarModalTitle');
    const modalContent = document.getElementById('calendarModalBody');
    const eventCount = document.getElementById('eventCount');

    modalTitle.textContent = `Eventos del ${fecha}`;

    if (eventos.length > 0) {
        modalContent.innerHTML = eventos.map(e => `
        <div class="p-4 mb-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-neutral-800 dark:to-neutral-900 shadow-md hover:shadow-lg transition">
            
            <!-- Título del evento -->
            <h3 class="font-bold text-lg flex items-center gap-2">${e.title}</h3>
            
            <!-- Descripción -->
            <p class="mt-1 text-sm leading-relaxed">${e.description ?? '<em">Sin descripción</em>'}</p>
            
            <!-- Hora -->
            <div class="mt-3 flex items-center text-sm">${e.start_time ?? 'No definido'}</div>
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
    modal.classList.add("hidden");
}




window.closeModal = closeModal;
