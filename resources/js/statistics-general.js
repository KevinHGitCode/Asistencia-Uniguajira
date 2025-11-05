// statistics-general.js

console.log('hola desde estadisticas.js');

// Configuración de gráficas generales
let generalCharts = {
    programAttendancesBar: null,
    programParticipantsPie: null,
    eventsOverTime: null,
    attendancesOverTime: null
};

// Función para detectar el tema actual dinámicamente
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

// Función para obtener opciones comunes basadas en el tema actual
function getCommonOptions() {
    const darkMode = isDarkMode();
    return {
        tooltip: {
            trigger: 'item',
            backgroundColor: darkMode ? 'rgba(50,50,50,0.7)' : 'rgba(255,255,255,0.9)',
            textStyle: { color: darkMode ? '#fff' : '#333' },
            borderColor: darkMode ? '#555' : '#ddd'
        },
        textStyle: {
            color: darkMode ? '#fff' : '#333'
        },
        legend: {
            textStyle: { color: darkMode ? '#fff' : '#333' }
        }
    };
}

function getEnhancedOptions() {
    const darkMode = isDarkMode();
    return {
        ...getCommonOptions(),
        toolbox: {
            feature: {
                saveAsImage: { title: 'Descargar', name: 'grafico', backgroundColor: darkMode ? '#1f2937' : '#ffffff' },
                restore: { title: 'Restaurar' },
                dataView: { title: 'Ver Datos', readOnly: false }
            },
            iconStyle: {
                borderColor: darkMode ? '#fff' : '#333'
            }
        },
        backgroundColor: darkMode ? '#1f2937' : '#ffffff',
        textStyle: {
            color: darkMode ? '#fff' : '#333'
        }
    };
}

window.createGeneralCharts = () => {
    const common = getEnhancedOptions();
    const darkMode = isDarkMode();

    // Gráfica de barras - Asistencias por Programa
    let programAttendancesBarEl = document.getElementById('chart_program_attendances_bar');
    if (programAttendancesBarEl) {
        if (generalCharts.programAttendancesBar) {
            echarts.dispose(programAttendancesBarEl);
        }
        generalCharts.programAttendancesBar = echarts.init(programAttendancesBarEl);
        fetch('/api/statistics/attendances-by-program')
            .then(response => response.json())
            .then( (data) => {
                generalCharts.programAttendancesBar.setOption(Object.assign({}, common, {
                    title: { 
                        text: 'Asistencias por Programa',
                        textStyle: { color: darkMode ? '#fff' : '#333' } 
                    },
                    xAxis: {
                        type: 'category',
                        data: data.map(item => item.program),
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                        splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
                    },
                    series: [{
                        type: 'bar',
                        data: data.map(item => item.count)
                    }]
                }));
            });
    }

    // Gráfica de pie - Participantes por Programa
    let programParticipantsPieEl = document.getElementById('chart_program_participants_pie');
    if (programParticipantsPieEl) {
        if (generalCharts.programParticipantsPie) {
            echarts.dispose(programParticipantsPieEl);
        }
        generalCharts.programParticipantsPie = echarts.init(programParticipantsPieEl);
        fetch('/api/statistics/participants-by-program')
            .then(response => response.json())
            .then( (data) => {
                generalCharts.programParticipantsPie.setOption(Object.assign({}, common, {
                    title: { 
                        text: 'Participantes por Programa', left: 'center',
                        textStyle: { color: darkMode ? '#fff' : '#333' }
                    },
                    series: [{
                        type: 'pie',
                        radius: '50%',
                        data: data.map(item => ({ value: item.count, name: item.program })),
                        label: { color: isDarkMode() ? '#fff' : '#333' }
                    }]
                }));
            });
    }

    // Gráfica de líneas - Eventos vs Tiempo
    let eventsOverTimeEl = document.getElementById('chart_events_time');
    if (eventsOverTimeEl) {
        if (generalCharts.eventsOverTime) {
            echarts.dispose(eventsOverTimeEl);
        }
        generalCharts.eventsOverTime = echarts.init(eventsOverTimeEl);
        fetch('/api/statistics/events-over-time')
            .then(response => response.json())
            .then( (data) => {
                generalCharts.eventsOverTime.setOption(Object.assign({}, common, {
                    title: { 
                        text: 'Eventos vs Tiempo',
                        textStyle: { color: darkMode ? '#fff' : '#333' }
                    },
                    xAxis: {
                        type: 'category',
                        data: data.map(item => item.date),
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                        splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
                    },
                    series: [{
                        type: 'line',
                        data: data.map(item => item.count)
                    }]
                }));
            });
    }

    // Gráfica de líneas - Asistencias vs Tiempo
    let attendancesOverTimeEl = document.getElementById('chart_attendances_time');
    if (attendancesOverTimeEl) {
        if (generalCharts.attendancesOverTime) {
            echarts.dispose(attendancesOverTimeEl);
        }
        generalCharts.attendancesOverTime = echarts.init(attendancesOverTimeEl);
        fetch('/api/statistics/attendances-over-time')
            .then(response => response.json())
            .then(data => {
                generalCharts.attendancesOverTime.setOption(Object.assign({}, common, {
                    title: { 
                        text: 'Asistencias vs Tiempo',
                        textStyle: { color: darkMode ? '#fff' : '#333' }
                    },
                    xAxis: {
                        type: 'category',
                        data: data.map(item => item.date),
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                        axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                        splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
                    },
                    series: [{
                        type: 'line',
                        data: data.map(item => item.count)
                    }]
                }));
            });
    }
}

