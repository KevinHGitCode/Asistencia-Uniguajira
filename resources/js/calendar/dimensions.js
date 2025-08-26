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
        // ✅ Pantallas muy grandes: medida estática
        cellSize = 35;
        gutter = 3;
    } 
    else if (containerWidth >= 1000) {
        // ✅ Ajuste dinámico para pantallas medianas/grandes
        // Calcular según el ancho, pero sin que aparezca scroll horizontal
        cellSize = Math.min(Math.max(containerWidth / 55, 18), 32);
        gutter = Math.max(cellSize * 0.1, 2);
    } 
    else {
        // ✅ Contenedores pequeños (< 1000px)
        // Se usan celdas más grandes para no perder visibilidad
        cellSize = Math.min(containerWidth / 18, 30);
        gutter = Math.max(cellSize * 0.12, 1);
    }

    return {
        cellSize: Math.floor(cellSize),
        gutter: Math.floor(gutter),
        containerWidth,
        containerHeight
    };
}
