// Obtenemos dimensiones responsivas para el calendario
export function getResponsiveDimensions() {
    const container = document.getElementById('cal-heatmap');
    if (!container) {
        return { cellSize: 20, gutter: 2, containerWidth: 0, containerHeight: 0 };
    }

    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;

    let cellSize, gutter;

    if (containerWidth >= 2000) {
        cellSize = 35;   // estático
        gutter = 3;
    }

    else if (containerWidth >= 1000) {
        // Ajuste dinámico al ancho disponible
        cellSize = Math.min(Math.max(containerWidth / 55, 18), 32);
        gutter = Math.max(cellSize * 0.1, 2);
    }
    
    else {
        cellSize = Math.min(containerWidth / 20, 28); // un poco más grande para no perder visibilidad
        gutter = Math.max(cellSize * 0.12, 1);
    }

    return {
        cellSize: Math.floor(cellSize),
        gutter: Math.floor(gutter),
        containerWidth,
        containerHeight
    };
}
