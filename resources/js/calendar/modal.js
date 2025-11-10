export function openModal(fecha, eventos) {
    const modal = document.getElementById('calendarModal');
    const modalTitle = document.getElementById('calendarModalTitle');
    const modalContent = document.getElementById('calendarModalBody');
    const eventCount = document.getElementById('eventCount');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    const [year, month, day] = fecha.split('-').map(Number);
    const fechaObj = new Date(year, month - 1, day);
    const fechaFormato = fechaObj.toLocaleDateString('es-CO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    modalTitle.textContent = `Eventos para el ${fechaFormato}`;
    
    if (eventos.length > 0) {
        // Obtener datos del usuario desde meta tags
        const userDependencyId = document.querySelector('meta[name="user-dependency-id"]')?.content;
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        const userRole = document.querySelector('meta[name="user-role"]')?.content;
        
        modalContent.innerHTML = eventos.map(e => {
            const isOwnEvent = e.user_id && userId && e.user_id.toString() === userId.toString();
            const isSameDependency = e.dependency_id && userDependencyId && 
                                     e.dependency_id.toString() === userDependencyId.toString();
            
            const isClickable = userRole === 'admin' || isOwnEvent || isSameDependency;
            const clickableClass = isClickable
                ? 'cursor-pointer hover:shadow-xl hover:scale-[1.02] transition-all duration-200' 
                : 'cursor-default';
            const dataAttr = isClickable ? `data-event-id="${e.id}"` : '';
            
            return `
                <div class="p-4 mb-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 shadow-lg ${clickableClass}" ${dataAttr}>
                    
                    <!-- Encabezado: título y botón "Ver" -->
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">${e.title}</h3>
                        ${isClickable ? `
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 
                                            text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-800">
                                <span class="text-xs font-medium">Ver detalles</span>
                                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" 
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" 
                                        clip-rule="evenodd"/>
                                </svg>
                            </div>
                        ` : ''}
                    </div>

                    <!-- "Tu evento" debajo del título -->
                    ${isOwnEvent ? `
                        <div class="mb-2 text-xs font-semibold px-2 py-1 w-fit rounded-md 
                                  text-gray-900 bg-zinc-50 dark:bg-zinc-900
                                    dark:text-white border border-gray-200 dark:border-zinc-800">
                            Tu evento
                        </div>
                    ` : ''}

                    <!-- Descripción -->
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${e.description ?? '<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <!-- Hora y ubicación -->
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${e.start_time ?? 'No definido'}</div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">📌 ${e.location ?? 'Ubicación no definida'}</div>
                </div>
            `;
        }).join('');
        
        // Listeners a los eventos clickeables
        const clickableEvents = modalContent.querySelectorAll('[data-event-id]');
        clickableEvents.forEach(eventDiv => {
            eventDiv.addEventListener('click', () => {
                const eventId = eventDiv.getAttribute('data-event-id');
                window.location.href = `/eventos/${eventId}`;
            });
        });
        
    } else {
        modalContent.innerHTML = `<p class="text-gray-600 dark:text-gray-400">No hay eventos registrados</p>`;
    }
    
    eventCount.textContent = `${eventos.length} evento${eventos.length !== 1 ? 's' : ''}`;
}

export function closeModal() {
    const modal = document.getElementById("calendarModal");
    modal.classList.remove('flex');
    modal.classList.add("hidden");
}

window.closeModal = closeModal;
