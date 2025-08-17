
// const cal = new CalHeatmap();
// // Detect theme from project (assuming you have a way to check, e.g., a class on body)
// const isDarkTheme = document.documentElement.classList.contains('dark');

// cal.paint({
//     domain: {
//         type: "month",
//         gutter: 1,
//         padding: [5, 5, 5, 5],
//         dynamicDimension: true,
//         sort: 'asc',
//         label: {
//             position: 'top'
//         },
//     },
//     subDomain: {
//         type: "xDay",
//         width: 30,
//         height: 30,
//         gutter: 5,
//         radius: 5,
//         label: 'D',
//         color: (t, v, backgroundColor) => {
//             if (isDarkTheme) {
//                 return 'white';
//             }
//             return 'black';
//         },
//     },
//     date: {
//         start: new Date(),
//         highlight: [
//             new Date('2025-08-19'),
//             new Date('2025-09-04'),
//             new Date('2025-10-05'),
//             new Date('2025-12-09'),
//             new Date(new Date().toLocaleString('en-CO', { timeZone: 'America/Bogota' })) // TODO: hay que terminar de pulir esta fecha de hoy para que no haya conflicto de zonas horarias
//         ],
//         locale: 'es',
//         timezone: 'America/Bogota'
//         },    
//         // range: 6,
//     theme: isDarkTheme ? 'dark' : 'light',
//     animationDuration: 2000
// });


// Para luego hacer responsive

// --- INICIO: Soporte para repintar el calendario con Livewire ---
let calInstance = null;
let isCalendarInitialized = false;

function getResponsiveDimensions() {
    const container = document.getElementById('cal-heatmap-container');
    if (!container) return { cellSize: 20, gutter: 2, containerWidth: 0, containerHeight: 0 };
    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;
    let cellSize = Math.min(Math.max(containerWidth / 45, 15), 30);
    let gutter = Math.max(cellSize * 0.1, 2);
    if (window.innerWidth < 768) {
        cellSize = Math.min(containerWidth / 20, 25);
        gutter = Math.max(cellSize * 0.1, 1);
    }
    return {
        cellSize: Math.floor(cellSize),
        gutter: Math.floor(gutter),
        containerWidth,
        containerHeight
    };
}

function paintCalendar() {
    const calContainer = document.getElementById('cal-heatmap');
    if (!calContainer) return;
    // Destruir instancia previa si existe
    if (calInstance) {
        calInstance.destroy();
        calInstance = null;
    }
    const isDarkTheme = document.documentElement.classList.contains('dark');
    const dimensions = getResponsiveDimensions();
    calInstance = new CalHeatmap();
    calInstance.paint({
        domain: {
            type: "month",
            gutter: dimensions.gutter,
            padding: [5, 5, 5, 5],
            dynamicDimension: true,
            sort: 'asc',
            label: {
                position: 'top'
            }
        },
        subDomain: {
            type: "xDay",
            width: dimensions.cellSize,
            height: dimensions.cellSize,
            gutter: dimensions.gutter,
            radius: Math.max(dimensions.cellSize * 0.1, 2),
            label: 'D',
            color: (timestamp, value, backgroundColor) => {
                if (isDarkTheme) {
                    return 'white';
                }
                return 'black';
            }
        },
        data: {
            source: [],
            type: 'json',
            x: 'date',
            y: 'count'
        },
        date: {
            start: new Date(),
            highlight: [
                new Date('2025-08-19'),
                new Date('2025-09-04'),
                new Date('2025-10-05'),
                new Date('2025-12-09'),
                new Date()
            ],
            locale: 'es',
            timezone: 'America/Bogota'
        },
        range: 6,
        theme: isDarkTheme ? 'dark' : 'light',
        animationDuration: 0,
        itemSelector: '#cal-heatmap',
        scale: {
            color: {
                type: 'threshold',
                scheme: 'Blues',
                domain: [1, 3, 5, 10]
            }
        }
    });
    isCalendarInitialized = true;
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    paintCalendar();
});

// Repintar al volver al dashboard con Livewire
window.addEventListener('dashboard:show', function() {
    setTimeout(() => {
        paintCalendar();
    }, 500);
});

// Repintar al cambiar el tamaño de la ventana (con debounce)
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        paintCalendar();
    }, 500);
});

// Repintar al cambiar el zoom (detectado mediante devicePixelRatio)
let lastZoom = window.devicePixelRatio;
setInterval(() => {
    if (window.devicePixelRatio !== lastZoom) {
        lastZoom = window.devicePixelRatio;
        paintCalendar();
    }
}, 500);

// Manejar cambios de tema
let lastTheme = document.documentElement.classList.contains('dark');
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            const newIsDarkTheme = document.documentElement.classList.contains('dark');
            if (newIsDarkTheme !== lastTheme) {
                lastTheme = newIsDarkTheme;
                paintCalendar();
            }
        }
    });
});
observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});
// --- FIN: Soporte para repintar el calendario con Livewire ---

