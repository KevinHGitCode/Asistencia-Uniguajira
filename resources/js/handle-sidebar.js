// handle-sidebar.js
let currentRoute = null;

function handleRouteChange(routeName) {
    if (currentRoute === routeName) return;

    currentRoute = routeName;
    console.log(`👉 Navegando a: ${routeName}`);

    switch (routeName) {
        // case 'dashboard':
        //     if (window._dashboardCalendarTimeout) {
        //         clearTimeout(window._dashboardCalendarTimeout);
        //     }
        //     window._dashboardCalendarTimeout = setTimeout(() => {
        //         if (typeof window.paintCalendar !== 'function') {
        //             import('./calendar/paint.js').then((module) => module.paintCalendar());
        //         } else {
        //             window.paintCalendar();
        //         }
        //     }, 100);
        //     break;

        case 'dashboard':
            if (window._dashboardCalendarTimeout) {
                clearTimeout(window._dashboardCalendarTimeout);
            }

            window._dashboardCalendarTimeout = setTimeout(async () => {
                const { paintCalendar } = await import('./calendar/paint.js');
                const { initCalendarObservers } = await import('./calendar/observers.js');

                const isDarkTheme = document.documentElement.classList.contains('dark');
                paintCalendar(isDarkTheme);
                initCalendarObservers();
            }, 100);

            break;

        case 'tipos':
            if (typeof window.paintCharts !== 'function') {
                import('./charts.js').then( () => window.paintCharts() );
            } else {
                window.paintCharts();
            }
            break;

        case 'estadisticas':
            // Esperar a que el DOM esté listo antes de inicializar
            requestAnimationFrame(() => {
                if (typeof window.createGeneralCharts !== 'function') {
                    import('./charts/general/index.js').then(() => {
                        window.createGeneralCharts();
                    });
                } else {
                    window.createGeneralCharts();
                }
            });
            break;

        case 'lista':
            break;

        case 'nuevo':
            break;

        case 'usuarios':
            break;

        default:
            const path = window.location.pathname;
            const eventMatch = path.match(/\/eventos\/(\d+)/);
            if (eventMatch) {
                const eventId = eventMatch[1];
                console.log(`Cargando gráficas para el evento con ID: ${eventId}`);
                if (typeof window.createEventCharts !== 'function') {
                    import('./charts-details-event.js').then(() => {
                        window.createEventCharts(eventId);
                    });
                } else {
                    window.createEventCharts(eventId);
                }
            } else {
                console.log("Ruta no definida en el manejador.");
            }
            break;
    }
}

document.addEventListener("livewire:navigated", () => {
    const path = window.location.pathname;
    const routeName = path.split('/').pop();
    handleRouteChange(routeName);
});
