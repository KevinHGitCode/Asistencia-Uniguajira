// Variable global para controlar si se deben recargar los gráficos
window.shouldReloadCharts = false;

console.log('hola desde charts.js');

// Guardamos las instancias globalmente
let charts = {
    bar: null,
    pie: null,
    line: null,
    stacked: null,
    radar: null,
    heatmap: null,
    kpi: null
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
            trigger: 'axis', 
            backgroundColor: darkMode ? 'rgba(50,50,50,0.7)' : 'rgba(255,255,255,0.9)',
            textStyle: { color: darkMode ? '#fff' : '#333' },
            borderColor: darkMode ? '#555' : '#ddd'
        },
        toolbox: { 
            feature: { saveAsImage: {} },
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

function createCharts() {
    const common = getCommonOptions();

    // ============ Bar Chart =============
    let barEl = document.getElementById('chart_bar');
    if (barEl) {
        if (charts.bar) {
            echarts.dispose(barEl);
        }
        charts.bar = echarts.init(barEl);
        charts.bar.setOption(Object.assign({}, common, {
            title: { 
                text: 'Bar Chart',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            xAxis: { 
                type: 'category', 
                data: ['Adm', 'Ing', 'Cont', 'TS', 'Ped', 'Neg Int'],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
            },
            yAxis: { 
                type: 'value',
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
            },
            series: [{ type: 'bar', data: [120, 200, 150, 80, 70, 110] }]
        }));
    }

    // ============ Pie Chart =============
    let pieEl = document.getElementById('chart_pie');
    if (pieEl) {
        if (charts.pie) {
            echarts.dispose(pieEl);
        }
        charts.pie = echarts.init(pieEl);
        charts.pie.setOption(Object.assign({}, common, {
            title: { 
                text: 'Pie Chart', 
                left: 'center',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            legend: {
                bottom: '5%',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            series: [{
                type: 'pie',
                radius: '50%',
                data: [
                    { value: 120, name: 'Ingeniería' },
                    { value: 80, name: 'Administración' },
                    { value: 90, name: 'Contaduría' },
                    { value: 70, name: 'Trabajo Social' },
                    { value: 60, name: 'Pedagogía' },
                    { value: 110, name: 'Neg. Int.' }
                ],
                label: {
                    color: isDarkMode() ? '#fff' : '#333'
                }
            }]
        }));
    }

    // ============ Line Chart =============
    let lineEl = document.getElementById('chart_line');
    if (lineEl) {
        if (charts.line) {
            echarts.dispose(lineEl);
        }
        charts.line = echarts.init(lineEl);
        charts.line.setOption(Object.assign({}, common, {
            title: { 
                text: 'Line Chart',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            legend: {
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            xAxis: { 
                type: 'category', 
                data: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
            },
            yAxis: { 
                type: 'value',
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
            },
            series: [
                { name: 'Adm', type: 'line', data: [20, 30, 40, 50] },
                { name: 'Ing', type: 'line', data: [30, 40, 30, 60] }
            ]
        }));
    }

    // ============ Stacked Bar Chart =============
    let stackedEl = document.getElementById('chart_stacked');
    if (stackedEl) {
        if (charts.stacked) {
            echarts.dispose(stackedEl);
        }
        charts.stacked = echarts.init(stackedEl);
        charts.stacked.setOption(Object.assign({}, common, {
            title: { 
                text: 'Presentes vs Ausentes',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            legend: { 
                data: ['Presentes', 'Ausentes'],
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            xAxis: { 
                type: 'category', 
                data: ['Ing', 'Adm', 'Cont'],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
            },
            yAxis: { 
                type: 'value',
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
            },
            series: [
                { name: 'Presentes', type: 'bar', stack: 'total', data: [80, 60, 70] },
                { name: 'Ausentes', type: 'bar', stack: 'total', data: [20, 40, 30] }
            ]
        }));
    }

    // ============ Radar Chart =============
    let radarEl = document.getElementById('chart_radar');
    if (radarEl) {
        if (charts.radar) {
            echarts.dispose(radarEl);
        }
        charts.radar = echarts.init(radarEl);
        charts.radar.setOption(Object.assign({}, common, {
            title: { 
                text: 'Radar Chart',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            radar: {
                indicator: [
                    { name: 'Puntualidad', max: 100 },
                    { name: 'Participación', max: 100 },
                    { name: 'Regularidad', max: 100 },
                    { name: 'Conclusión', max: 100 }
                ],
                name: {
                    textStyle: { color: isDarkMode() ? '#fff' : '#333' }
                },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } },
                splitLine: { lineStyle: { color: isDarkMode() ? '#333' : '#f0f0f0' } }
            },
            series: [{
                type: 'radar',
                data: [{ value: [90, 80, 70, 60], name: 'Perfil Carrera' }]
            }]
        }));
    }

    // ============ Heatmap =============
    let heatmapEl = document.getElementById('chart_heatmap');
    if (heatmapEl) {
        if (charts.heatmap) {
            echarts.dispose(heatmapEl);
        }
        charts.heatmap = echarts.init(heatmapEl);
        charts.heatmap.setOption(Object.assign({}, common, {
            title: { 
                text: 'Heatmap Asistencia',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            xAxis: { 
                type: 'category', 
                data: ['Lun','Mar','Mié','Jue','Vie'],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
            },
            yAxis: { 
                type: 'category', 
                data: ['Mañana','Tarde'],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' },
                axisLine: { lineStyle: { color: isDarkMode() ? '#555' : '#ddd' } }
            },
            visualMap: { 
                min: 0, 
                max: 100, 
                calculable: true,
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            series: [{
                type: 'heatmap',
                data: [
                    [0,0,50],[1,0,80],[2,0,60],[3,0,70],[4,0,90],
                    [0,1,40],[1,1,60],[2,1,50],[3,1,80],[4,1,70]
                ]
            }]
        }));
    }

    // ============ KPI Card (Gauge) =============
    let kpiEl = document.getElementById('chart_kpi');
    if (kpiEl) {
        if (charts.kpi) {
            echarts.dispose(kpiEl);
        }
        charts.kpi = echarts.init(kpiEl);
        charts.kpi.setOption(Object.assign({}, common, {
            title: { 
                text: 'KPI: % Asistencia General',
                textStyle: { color: isDarkMode() ? '#fff' : '#333' }
            },
            series: [{
                type: 'gauge',
                progress: { show: true },
                detail: { 
                    valueAnimation: true, 
                    formatter: '{value}%',
                    color: isDarkMode() ? '#fff' : '#333'
                },
                data: [{ value: 76, name: 'Asistencia' }],
                axisLabel: { color: isDarkMode() ? '#fff' : '#333' }
            }]
        }));
    }
}

// 🔥 Función pública para Livewire
window.paintCharts = () => {
    createCharts();
};

// 🔥 Función para actualizar el tema de los gráficos
window.updateChartsTheme = () => {
    createCharts(); // Recrea los gráficos con el nuevo tema
};

// 🔥 Observer para detectar cambios en la clase 'dark'
const themeObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            const isDark = document.documentElement.classList.contains('dark');
            // Pequeño delay para asegurar que Alpine.js termine sus actualizaciones
            setTimeout(() => {
                createCharts();
            }, 50);
        }
    });
});

// Observar cambios en el elemento html
themeObserver.observe(document.documentElement, { 
    attributes: true, 
    attributeFilter: ['class'] 
});

// 🔥 Redimensionar sin recrear todo
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        Object.values(charts).forEach(chart => {
            if (chart) chart.resize();
        });
    }, 200);
});

// Cleanup al salir de la página
window.addEventListener('beforeunload', () => {
    themeObserver.disconnect();
    Object.values(charts).forEach(chart => {
        if (chart) {
            chart.dispose();
        }
    });
});