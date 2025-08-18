// handle-sidebar.js
let currentRoute = null;

function handleRouteChange(routeName) {
    if (currentRoute === routeName) return; // evitar duplicados

    currentRoute = routeName;
    // console.clear();
    console.log(`👉 Navegando a: ${routeName}`);

    switch (routeName) {
        case 'dashboard':
            // Debounce para evitar repintados dobles
            if (window._dashboardCalendarTimeout) {
                clearTimeout(window._dashboardCalendarTimeout);
            }
            window._dashboardCalendarTimeout = setTimeout(() => {
                if (typeof window.paintCalendar !== 'function') {
                    import('./calendar.js').then(() => window.paintCalendar());
                } else {
                    window.paintCalendar();
                }
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
            // console.log("Cargar estadísticas...");
            break;

        case 'lista':
            // console.log("Mostrar lista de eventos...");
            break;
        
        case 'nuevo':
            // console.log("Mostrar lista de eventos...");
            break;

        case 'usuarios':
            // console.log("Mostrar lista de usuarios...");
            break;

        default:
            console.log("Ruta no definida en el manejador.");
    }
}

// Cuando Livewire navega con wire:navigate
document.addEventListener("livewire:navigated", () => {
    // Obtener la ruta actual desde la URL
    const path = window.location.pathname;
    const routeName = path.split('/').pop(); 
    handleRouteChange(routeName);
});
