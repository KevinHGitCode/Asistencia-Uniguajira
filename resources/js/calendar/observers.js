import { paintCalendar, destroyCalendar } from './paint.js';
import { cleanupCalendarObservers } from './cleanup.js';

let lastZoom = window.devicePixelRatio;
let zoomCheckInterval, resizeTimeout;
let lastTheme = document.documentElement.classList.contains('dark');
const themeObserver = new MutationObserver(handleThemeChange);

export function initCalendarObservers() {
    startZoomMonitoring();
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('resize', handleResize);
    document.addEventListener('livewire:navigated', handleLivewireNav);
}

function startZoomMonitoring() {
    zoomCheckInterval = setInterval(() => {
        const currentZoom = window.devicePixelRatio;
        if (Math.abs(currentZoom - lastZoom) > 0.1) {
            lastZoom = currentZoom;
        }
    }, 500);
}

function handleResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => paintCalendar(), 300);
}

function handleThemeChange(mutations) {
    for (const mutation of mutations) {
        if (mutation.attributeName === 'class') {
            const newTheme = document.documentElement.classList.contains('dark');
            if (newTheme !== lastTheme) {
                lastTheme = newTheme;
            }
        }
    }
}

function handleLivewireNav() {
    if (window.location.pathname.includes('dashboard')) {
        paintCalendar();
    } else {
        cleanupCalendarObservers();
        destroyCalendar();
    }
}
