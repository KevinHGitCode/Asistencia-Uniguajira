// Función para limpiar completamente el contenedor del calendario
export function clearCalendarContainer() {
    const calContainer = document.getElementById('cal-heatmap');
    if (calContainer) {
        calContainer.innerHTML = '';
        calContainer.removeAttribute('style');
    }
}

export async function fetchEventsAndPaintCalendar() {
    const response = await fetch('/api/eventos-json');
    const eventos = await response.json();
    return eventos;
}

export async function fetchMyEventsAndPaintCalendar() {
    const response = await fetch('/api/mis-eventos-json');
    if (!response.ok) {
        console.error("Error al traer eventos", response.status);
        return [];
    }
    const data = await response.json();
    console.log("🔑 Auth:", data.auth_id);
    return data.eventos;
}

// Función para determinar el semestre y las fechas de inicio
export const getSemesterInfo = () => {
    const now = new Date();
    const currentMonth = now.getMonth(); // 0-11
    const currentYear = now.getFullYear();

    let startDate, range;

    if (currentMonth >= 0 && currentMonth <= 5) {
        // Primer semestre (Enero - Junio)
        startDate = new Date(currentYear, 0, 1); // 1 de enero
        range = 6; // 6 meses
    } else {
        // Segundo semestre (Julio - Diciembre)
        startDate = new Date(currentYear, 6, 1); // 1 de julio
        range = 6; // 6 meses
    }

    return { startDate, range };
};