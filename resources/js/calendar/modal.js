export function openModal(fecha, eventos) {
    window.selectedCalendarDate = fecha;

    const modal = document.getElementById('calendarModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    window.dispatchEvent(new CustomEvent('calendar-modal-opened', { detail: { date: fecha } }));

    const modalTitle = document.getElementById('calendarModalTitle');
    const modalContent = document.getElementById('calendarModalBody');
    const eventCount = document.getElementById('eventCount');

    const [year, month, day] = fecha.split('-').map(Number);
    const fechaObj = new Date(year, month - 1, day);
    const fechaFormato = fechaObj.toLocaleDateString('es-CO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    modalTitle.textContent = `Eventos para el ${fechaFormato}`;

    if (eventos.length > 0) {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        const userRole = document.querySelector('meta[name="user-role"]')?.content;
        const hasAdminAccess = userRole === 'admin' || userRole === 'superadmin';

        const formatTo12h = (time) => {
            if (!time) return 'No definido';
            const [h, m] = time.split(':').map(Number);
            const suffix = h >= 12 ? 'PM' : 'AM';
            const hour12 = h % 12 || 12;
            return `${hour12}:${String(m).padStart(2, '0')} ${suffix}`;
        };

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        modalContent.innerHTML = eventos.map((e) => {
            const isOwnEvent = e.user_id && userId && e.user_id.toString() === userId.toString();
            const isClickable = Boolean(e.can_view && e.show_url);
            const cardTag = isClickable ? 'a' : 'div';
            const hrefAttr = isClickable ? `href="${escapeHtml(e.show_url)}"` : '';
            const clickableClass = isClickable
                ? 'cursor-pointer hover:shadow-xl hover:scale-[1.02] hover:border-[#62a9b6] transition-all duration-200'
                : 'cursor-default';

            const metaBadges = [];

            if (e.campus_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.108 7.5-11.25a7.5 7.5 0 10-15 0C4.5 16.892 12 21 12 21z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Sede: ${escapeHtml(e.campus_name)}
                    </span>
                `);
            }

            if (e.dependency_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#cc5e50] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        ${escapeHtml(e.dependency_name)}
                    </span>
                `);
            }

            if (e.area_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        ${escapeHtml(e.area_name)}
                    </span>
                `);
            }

            if (hasAdminAccess && e.creator_name) {
                metaBadges.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#e2a542] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Creado por: ${escapeHtml(e.creator_name)}
                    </span>
                `);
            }

            const metaBadgesHtml = metaBadges.length > 0
                ? `<div class="flex flex-wrap gap-2 mb-3">${metaBadges.join('')}</div>`
                : '';

            return `
                <${cardTag} ${hrefAttr} class="block p-4 mb-4 rounded-2xl border border-transparent bg-white dark:bg-zinc-800 shadow-lg ${clickableClass}">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start mb-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg leading-snug text-gray-900 dark:text-white">${escapeHtml(e.title)}</h3>
                            ${isOwnEvent ? `
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md text-gray-900 bg-zinc-50 dark:bg-zinc-900 dark:text-white border border-gray-200 dark:border-zinc-800">
                                    Tu evento
                                </div>
                            ` : ''}
                            ${!isOwnEvent && e.is_dependency_event ? `
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md bg-green-900 text-white">
                                    Tu dependencia
                                </div>
                            ` : ''}
                        </div>

                        ${isClickable ? `
                            <div class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#62a9b6]/10 text-gray-900 dark:text-white border border-[#62a9b6]/30">
                                <span class="text-xs font-medium">Ver detalles</span>
                                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        ` : ''}
                    </div>

                    ${metaBadgesHtml}

                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${e.description ? escapeHtml(e.description) : '<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${formatTo12h(e.start_time)}</div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">📌 ${escapeHtml(e.location ?? 'Ubicación no definida')}</div>
                </${cardTag}>
            `;
        }).join('');
    } else {
        modalContent.innerHTML = '<p class="text-gray-600 dark:text-gray-400">No hay eventos registrados</p>';
    }

    eventCount.textContent = `${eventos.length} evento${eventos.length !== 1 ? 's' : ''}`;
}

export function closeModal() {
    const modal = document.getElementById('calendarModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

window.closeModal = closeModal;
