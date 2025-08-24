
// Obtenemos dimensiones responsivas para el calendario
export function getResponsiveDimensions() {
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