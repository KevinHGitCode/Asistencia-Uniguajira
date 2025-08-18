
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


async function fetchEventsAndPaintCalendar() {
    const response = await fetch('/api/eventos-json');
    const eventos = await response.json();
    return eventos;
}


window.paintCalendar = async () => {
    const calContainer = document.getElementById('cal-heatmap');
    if (!calContainer) return;

    // Destruir instancia previa si existe
    if (calInstance) {
        calInstance.destroy();
        calInstance = null;
    }

    const isDarkTheme = document.documentElement.classList.contains('dark');
    console.log(`Repaint calendar: isDarkTheme=${isDarkTheme}`);

    const events = await fetchEventsAndPaintCalendar();
    const dimensions = getResponsiveDimensions();

    // Función para determinar el semestre y las fechas de inicio
    const getSemesterInfo = () => {
        const now = new Date();
        const currentMonth = now.getMonth(); // 0-11
        const currentYear = now.getFullYear();

        let startDate, range;

        if (currentMonth >= 0 && currentMonth <= 5) {
            // Primer semestre (Enero - Junio)
            startDate = new Date(currentYear, 0, 1); // 1 de enero
            range = 6; // 6 meses
        } else {
            // Segundo semestre (Julio - Diciembre)
            startDate = new Date(currentYear, 6, 1); // 1 de julio
            range = 6; // 6 meses
        }

        return { startDate, range };
    };

    const semesterInfo = getSemesterInfo();

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
            source: events,
            type: 'json',
            x: 'date',
            y: 'count'
        },
        date: {
            start: semesterInfo.startDate, // Fecha de inicio del semestre
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
        range: semesterInfo.range, // 6 meses por semestre
        theme: isDarkTheme ? 'dark' : 'light',
        animationDuration: 1000,
        itemSelector: '#cal-heatmap',
        scale: {
            color: {
                type: 'threshold',
                domain: [1, 3, 5, 10],
                range: isDarkTheme
                    ? ['#22223b', '#4a4e69', '#9a8c98', '#f2e9e4', '#ffbe76'] // Ejemplo modo oscuro
                    : ['#e3eafc', '#b6ccfe', '#7ea6f6', '#4f83cc', '#274690'] // Azules suaves modo claro
            }
        }
    });

    isCalendarInitialized = true;
};


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
            }
        }
    });
});
observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});
// --- FIN: Soporte para repintar el calendario con Livewire ---

