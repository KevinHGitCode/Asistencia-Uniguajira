
import { getResponsiveDimensions } from './dimensions.js';
import { clearCalendarContainer, 
            fetchEventsAndPaintCalendar,
            getSemesterInfo
        } from './utils.js';

// --- INICIO: Soporte para repintar el calendario con Livewire ---
let calInstance = null;
let isCalendarInitialized = false;
let isCalendarPainting = false; // Flag para prevenir múltiples ejecuciones simultáneas


window.paintCalendar = async () => {
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

        const isDarkTheme = document.documentElement.classList.contains('dark');
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

// Limpiar observadores cuando salgamos del dashboard
function cleanupCalendarObservers() {
    if (zoomCheckInterval) {
        clearInterval(zoomCheckInterval);
        zoomCheckInterval = null;
    }
    themeObserver.disconnect();
    
    if (repaintTimeout) {
        clearTimeout(repaintTimeout);
    }
    if (resizeTimeout) {
        clearTimeout(resizeTimeout);
    }
    
    console.log('📅 Calendar observers cleaned up');
}

// Inicializar observadores solo cuando estemos en dashboard
function initCalendarObservers() {
    if (isInDashboard()) {
        startZoomMonitoring();
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        console.log('📅 Calendar observers initialized');
    }
}

// Repintar al cambiar el zoom (con mejor detección)
let lastZoom = window.devicePixelRatio;
let zoomCheckInterval;

function startZoomMonitoring() {
    if (zoomCheckInterval) {
        clearInterval(zoomCheckInterval);
    }
    
    zoomCheckInterval = setInterval(() => {
        if (!isInDashboard()) return;
        
        const currentZoom = window.devicePixelRatio;
        if (Math.abs(currentZoom - lastZoom) > 0.1) {
            console.log(`📅 Zoom changed from ${lastZoom} to ${currentZoom}`);
            lastZoom = currentZoom;
            debouncedRepaint(400);
        }
    }, 500);
}


// Debounced repaint function
let repaintTimeout;
function debouncedRepaint(delay = 300) {
    if (!isInDashboard()) return;
    
    clearTimeout(repaintTimeout);
    repaintTimeout = setTimeout(() => {
        if (isCalendarInitialized && !isCalendarPainting) {
            paintCalendar();
        }
    }, delay);
}

// Manejar cambios de tema (mejorado)
let lastTheme = document.documentElement.classList.contains('dark');
const themeObserver = new MutationObserver(function(mutations) {
    if (!isInDashboard()) return;
    
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            const newIsDarkTheme = document.documentElement.classList.contains('dark');
            if (newIsDarkTheme !== lastTheme) {
                lastTheme = newIsDarkTheme;
                console.log(`📅 Theme changed to: ${newIsDarkTheme ? 'dark' : 'light'}`);
                debouncedRepaint(200);
            }
        }
    });
});

function isInDashboard() {
    return window.location.pathname === '/dashboard' || 
           window.location.pathname.endsWith('/dashboard');
}

// Repintar al cambiar el tamaño de la ventana (con debounce mejorado)
let resizeTimeout;
window.addEventListener('resize', function() {
    if (!isInDashboard()) return;
    
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        debouncedRepaint(500);
    }, 300);
});


// Manejar navegación de Livewire
document.addEventListener('livewire:navigated', function() {
    if (isInDashboard()) {
        // Estamos en dashboard, inicializar
        setTimeout(() => {
            initCalendarObservers();
            if (!isCalendarInitialized && !isCalendarPainting) {
                paintCalendar();
            }
        }, 100);
    } else {
        // No estamos en dashboard, limpiar
        cleanupCalendarObservers();
        if (calInstance) {
            try {
                calInstance.destroy();
            } catch (error) {
                console.warn('Error cleaning up calendar:', error);
            }
            calInstance = null;
        }
        isCalendarInitialized = false;
        isCalendarPainting = false;
    }
});

// Limpieza final cuando la página se descarga
window.addEventListener('beforeunload', function() {
    cleanupCalendarObservers();
    if (calInstance) {
        try {
            calInstance.destroy();
        } catch (error) {
            console.warn('Error during final cleanup:', error);
        }
    }
});

// --- FIN: Soporte para repintar el calendario con Livewire ---