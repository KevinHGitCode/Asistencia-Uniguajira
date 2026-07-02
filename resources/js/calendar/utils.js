// Función para limpiar completamente el contenedor del calendario
export function clearCalendarContainer() {
    const calContainer = document.getElementById('cal-heatmap');
    if (calContainer) {
        calContainer.innerHTML = '';
        calContainer.removeAttribute('style');
    }
}

function periodParams(period = null) {
    const params = new URLSearchParams();

    if (period?.year && period?.semester) {
        params.set('year', period.year);
        params.set('semester', period.semester);
    }

    return params;
}

export async function fetchEventsAndPaintCalendar(period = null) {
    const params = periodParams(period);
    params.set('include_navigation', '1');

    const response = await fetch(`/api/eventos-json?${params.toString()}`);
    const payload = await response.json();

    if (Array.isArray(payload)) {
        return { events: payload, period: getSemesterInfo(), navigation: null };
    }

    return payload;
}

export async function fetchMyEventsAndPaintCalendar(period = null) {
    const params = periodParams(period);
    const query = params.toString();
    const response = await fetch(`/api/mis-eventos-json${query ? `?${query}` : ''}`);
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

    const semester = currentMonth >= 0 && currentMonth <= 5 ? 1 : 2;

    return {
        year: currentYear,
        semester,
        label: `Semestre ${semester === 1 ? 'I' : 'II'} ${currentYear}`,
        start: startDate.toISOString().split('T')[0],
        startDate,
        range
    };
};
