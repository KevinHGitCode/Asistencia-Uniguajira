export function openModal(fecha, eventos) {

    window.selectedCalendarDate = fecha;
    
    const modal = document.getElementById('calendarModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Despachar evento con la fecha
    window.dispatchEvent(new CustomEvent('calendar-modal-opened', { detail: { date: fecha } }));

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
        const isAdmin = userRole === 'admin';
        
        // Helper para formatear hora a 12h
        const formatTo12h = (time) => {
            if (!time) return 'No definido';
            const [h, m] = time.split(':').map(Number);
            const suffix = h >= 12 ? 'PM' : 'AM';
            const hour12 = h % 12 || 12;
            return `${hour12}:${String(m).padStart(2, '0')} ${suffix}`;
        };

        modalContent.innerHTML = eventos.map(e => {
            const isOwnEvent = e.user_id && userId && e.user_id.toString() === userId.toString();
            const isSameDependency = e.dependency_id && userDependencyId && 
                                     e.dependency_id.toString() === userDependencyId.toString();
            
            const isClickable = isAdmin || isOwnEvent || isSameDependency;
            const clickableClass = isClickable
                ? 'cursor-pointer hover:shadow-xl hover:scale-[1.02] transition-all duration-200' 
                : 'cursor-default';
            const dataAttr = isClickable ? `data-event-id="${e.id}"` : '';
            
            // Construir badges de metadata (dependencia, área, creador)
            const metaBadges = [];

            // Badge de dependencia
            if (e.dependency_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#cc5e50] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        ${e.dependency_name}
                    </span>
                `);
            }

            // Badge de área
            if (e.area_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        ${e.area_name}
                    </span>
                `);
            }

            // Badge de creador (solo visible para admin)
            if (isAdmin && e.creator_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#e2a542] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Creado por: ${e.creator_name}
                    </span>
                `);
            }

            const metaBadgesHtml = metaBadges.length > 0
                ? `<div class="flex flex-wrap gap-2 mb-2">${metaBadges.join('')}</div>`
                : '';

            return `
                <div class="p-4 mb-4 rounded-2xl bg-white dark:bg-zinc-800 shadow-lg ${clickableClass}" ${dataAttr}>
                    
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

                    <!-- Badges: Dependencia, Área, Creador -->
                    ${metaBadgesHtml}

                    <!-- Descripción -->
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${e.description ?? '<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <!-- Hora y ubicación -->
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${formatTo12h(e.start_time)}</div>
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