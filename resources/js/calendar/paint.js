
import { getResponsiveDimensions } from './dimensions.js';
import { clearCalendarContainer, 
            fetchEventsAndPaintCalendar,
            fetchMyEventsAndPaintCalendar,
            getSemesterInfo
        } from './utils.js';
import { openModal } from './modal.js';
// import CalHeatmap from 'cal-heatmap';

// // Importar los estilos básicos
// import 'cal-heatmap/cal-heatmap.css';

// // Importar plugins que necesites
// import Legend from 'cal-heatmap/plugins/Legend';
// import Tooltip from 'cal-heatmap/plugins/Tooltip';



// --- INICIO: Soporte para repintar el calendario con Livewire ---
let calInstance = null;
let isCalendarInitialized = false;
let isCalendarPainting = false; // Flag para prevenir múltiples ejecuciones simultáneas


export async function paintCalendar(forcedTheme = null) {
    // Prevenir múltiples ejecuciones simultáneas
    if (isCalendarPainting) {
        console.log('📅 Calendar already painting, skipping...');
        return;
    }

    const calContainer = document.getElementById('cal-heatmap');
    if (!calContainer) {
        console.log('📅 Calendar container not found');
        return;
    }

    isCalendarPainting = true;
    console.log('📅 Starting calendar paint...');

    try {
        // Destruir instancia previa si existe
        if (calInstance) {
            try {
                calInstance.destroy();
            } catch (error) {
                console.warn('Error destroying calendar:', error);
            }
            calInstance = null;
        }
        
        clearCalendarContainer();

        const isDarkTheme = forcedTheme ?? document.documentElement.classList.contains('dark');
        console.log(`📅 Painting calendar: isDarkTheme=${isDarkTheme}`);

        const events = await fetchEventsAndPaintCalendar();
        const dimensions = getResponsiveDimensions();


        const myevents = await fetchMyEventsAndPaintCalendar();
        console.log(myevents);

        const highlightDates = myevents.map(e => new Date(e.date));

        const semesterInfo = getSemesterInfo();

        calInstance = new CalHeatmap();
        await calInstance.paint({
            domain: {
                type: "month",
                gutter: dimensions.gutter,
                padding: [5, 5, 5, 5],
                dynamicDimension: true,
                sort: 'asc',
                label: {
                    position: 'top',
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
                start: semesterInfo.startDate,
                highlight: highlightDates, // Resaltar mis eventos,
                locale: 'es',
                timezone: 'America/Bogota'
            },
            range: semesterInfo.range,
            theme: isDarkTheme ? 'dark' : 'light',
            animationDuration: 1000,
            itemSelector: '#cal-heatmap',
            scale: {
                color: {
                    type: 'threshold',
                    domain: [1, 3, 5, 10],
                    range: isDarkTheme
                        ? ['#03090f', '#62a9b6', '#cc5e50', '#e2a542', '#f2e9e4'] // tonos más intensos para oscuro
                        : ['#f2e9e4', '#e2a542', '#cc5e50', '#62a9b6', '#03090f'] // tonos más claros para fondo blanco

                }
            }
        });

        calInstance.on('click', async (event, timestamp, value) => {
            const date = new Date(timestamp).toISOString().split('T')[0]; // YYYY-MM-DD
            
            try {
                const response = await fetch(`/api/events/${date}`);
                const eventos = await response.json();

                openModal(date, eventos);
            } catch (error) {
                console.error('Error al obtener eventos:', error);
            }
        });


        isCalendarInitialized = true;
        console.log('📅 Calendar painted successfully');

        

    } catch (error) {
        console.error('📅 Error painting calendar:', error);
        clearCalendarContainer();
        calInstance = null;
        isCalendarInitialized = false;
    } finally {
        isCalendarPainting = false;
    }
};

export function destroyCalendar() {
    if (calInstance) {
        try { calInstance.destroy(); } catch (e) {}
        calInstance = null;
    }
}