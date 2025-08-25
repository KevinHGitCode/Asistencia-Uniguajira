
import { getResponsiveDimensions } from './dimensions.js';
import { clearCalendarContainer, 
            fetchEventsAndPaintCalendar,
            getSemesterInfo
        } from './utils.js';

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
                start: semesterInfo.startDate,
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
            range: semesterInfo.range,
            theme: isDarkTheme ? 'dark' : 'light',
            animationDuration: 1000,
            itemSelector: '#cal-heatmap',
            scale: {
                color: {
                    type: 'threshold',
                    domain: [1, 3, 5, 10],
                    range: isDarkTheme
                        ? ['#22223b', '#4a4e69', '#9a8c98', '#f2e9e4', '#ffbe76']
                        : ['#e3eafc', '#b6ccfe', '#7ea6f6', '#4f83cc', '#274690']
                }
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