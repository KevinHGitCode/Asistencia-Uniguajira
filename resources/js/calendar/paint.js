import { getResponsiveDimensions } from './dimensions.js';
import { clearCalendarContainer, 
            fetchEventsAndPaintCalendar,
            fetchMyEventsAndPaintCalendar,
            getSemesterInfo
        } from './utils.js';
import { openModal } from './modal.js';

function getTodayAsDate() {
    const today = new Date();
    const localISODate = new Date(today.getTime() - today.getTimezoneOffset() * 60000)
        .toISOString()
        .split('T')[0];
    
    return new Date(localISODate);
}

let calInstance = null;
let isCalendarInitialized = false;
let isCalendarPainting = false;
let pendingPaintTheme = null;

export async function paintCalendar(forcedTheme = null) {
    if (isCalendarPainting) {
        pendingPaintTheme = forcedTheme;
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
        const today = getTodayAsDate();

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
                    // Verificar si la celda tiene datos (value > 0) o está destacada
                    const date = new Date(timestamp);
                    const isHighlighted = highlightDates.some(d => 
                        d.getFullYear() === date.getFullYear() &&
                        d.getMonth() === date.getMonth() &&
                        d.getDate() === date.getDate()
                    ) || (date.getTime() === today.getTime());
                    
                    // Si tiene datos o está destacada, texto blanco
                    if (value > 0 || isHighlighted) {
                        return 'white';
                    }
                    
                    // Si no, usar el color según el tema
                    return isDarkTheme ? 'white' : 'black';
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
                highlight: [
                    ...highlightDates,
                    today
                ],
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
                        ? ['#03090f', '#62a9b6', '#cc5e50', '#e2a542', '#f2e9e4']
                        : ['#03090f', '#62a9b6', '#cc5e50', '#e2a542', '#f2e9e4']
                }
            }
        });

        // 👉 Reemplaza el bloque anterior por este
        setTimeout(() => {
            const calContainer = document.getElementById('cal-heatmap');
            const highlightRects = calContainer.querySelectorAll('rect.highlight');
            console.log('🎯 Rectángulos highlight encontrados:', highlightRects.length);

            // Fecha "hoy" en local midnight (para comparar a nivel día)
            const todayLocal = today;
            todayLocal.setHours(0, 0, 0, 0);

            let marked = false;

            // 1) Intento preferido: usar rect.__data__.t (timestamp en ms) si está disponible
            highlightRects.forEach((rect, i) => {
                const d = rect.__data__;
                if (!d || typeof d.t !== 'number') return;

                const rectDate = new Date(d.t); // d.t está en ms
                const rectLocalMidnight = new Date(rectDate.getFullYear(), rectDate.getMonth(), rectDate.getDate());
                
                console.log(`Rect #${i} -> rectDate local: ${rectLocalMidnight.toISOString()}`);

                if (rectLocalMidnight.getTime() === todayLocal.getTime()) {
                    rect.classList.add('today-highlight');
                    console.log('✅ Día actual encontrado y marcado en rect #' + i);
                    marked = true;
                }
            });

            if (!marked) {
                console.warn('⚠️ No se encontró la celda del día de hoy entre los highlights.');
            }
        }, 150);



        // Evento click
        calInstance.on('click', async (event, timestamp, value) => {
            const date = new Date(timestamp).toISOString().split('T')[0];
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

        if (pendingPaintTheme !== null) {
            const nextTheme = pendingPaintTheme;
            pendingPaintTheme = null;
            setTimeout(() => paintCalendar(nextTheme), 0);
        }
    }
};

export function destroyCalendar() {
    if (calInstance) {
        try { calInstance.destroy(); } catch (e) {}
        calInstance = null;
    }
}
