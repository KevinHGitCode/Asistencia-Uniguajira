export function getResponsiveDimensions() {
    const container = document.getElementById('cal-heatmap');
    if (!container) {
        console.log("📏 getResponsiveDimensions: contenedor no encontrado");
        return { cellSize: 20, gutter: 2, containerWidth: 0, containerHeight: 0 };
    }

    const containerWidth = container.offsetWidth;
    const containerHeight = container.offsetHeight;

    let cellSize, gutter;

    if (containerWidth >= 2000) {
        cellSize = 35;
        gutter = 3;
        console.log("📏 Modo: Pantalla muy grande");
    } 
    else if (containerWidth >= 1500) {
        cellSize = Math.min(Math.max(containerWidth / 55, 20), 34);
        gutter = Math.max(cellSize * 0.1, 2.5);
        console.log("📏 Modo: Pantalla grande");
    }
    else if (containerWidth >= 940) { 
        // Ajuste del tamaño de celda para mostrar los 6 meses completos
        cellSize = Math.min(Math.max(containerWidth / 52, 14), 26);
        gutter = Math.max(cellSize * 0.1, 2);
        console.log("📏 Modo: Pantalla mediana (>= 940px)");
    }

    else {
        cellSize = Math.min(containerWidth / 18, 30);
        gutter = Math.max(cellSize * 0.12, 1);
        console.log("📏 Modo: Pantalla pequeña");
    }


    const result = {
        cellSize: Math.floor(cellSize),
        gutter: Math.floor(gutter),
        containerWidth,
        containerHeight
    };

    console.log("📏 Dimensiones calculadas:", result);

    return result;
}
