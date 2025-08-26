export function cleanupCalendarObservers() {
    if (window.zoomCheckInterval) clearInterval(window.zoomCheckInterval);
    if (window.resizeTimeout) clearTimeout(window.resizeTimeout);
    if (window.repaintTimeout) clearTimeout(window.repaintTimeout);
    console.log("📅 Calendar observers cleaned up");
}
