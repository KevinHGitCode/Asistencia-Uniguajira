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

// Opciones comunes
const common = {
    tooltip: { trigger: 'axis', backgroundColor: 'rgba(50,50,50,0.7)', textStyle: { color: '#fff' } },
    toolbox: { feature: { saveAsImage: {} } },
    darkMode: document.documentElement.classList.contains('dark')
};

function createCharts() {

    // ============ Bar Chart =============
    let barEl = document.getElementById('chart_bar');
    if (barEl) {
        echarts.dispose(barEl);
        charts.bar = echarts.init(barEl);
        charts.bar.setOption(Object.assign({}, common, {
            title: { text: 'Bar Chart' },
            xAxis: { type: 'category', data: ['Adm', 'Ing', 'Cont', 'TS', 'Ped', 'Neg Int'] },
            yAxis: { type: 'value' },
            series: [{ type: 'bar', data: [120, 200, 150, 80, 70, 110] }]
        }));
    }

    // ============ Pie Chart =============
    let pieEl = document.getElementById('chart_pie');
    if (pieEl) {
        echarts.dispose(pieEl);
        charts.pie = echarts.init(pieEl);
        charts.pie.setOption(Object.assign({}, common, {
            title: { text: 'Pie Chart', left: 'center' },
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
                ]
            }]
        }));
    }

    // ============ Line Chart =============
    let lineEl = document.getElementById('chart_line');
    if (lineEl) {
        echarts.dispose(lineEl);
        charts.line = echarts.init(lineEl);
        charts.line.setOption(Object.assign({}, common, {
            title: { text: 'Line Chart' },
            xAxis: { type: 'category', data: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'] },
            yAxis: { type: 'value' },
            series: [
                { name: 'Adm', type: 'line', data: [20, 30, 40, 50] },
                { name: 'Ing', type: 'line', data: [30, 40, 30, 60] }
            ]
        }));
    }

    // ============ Stacked Bar Chart =============
    let stackedEl = document.getElementById('chart_stacked');
    if (stackedEl) {
        echarts.dispose(stackedEl);
        charts.stacked = echarts.init(stackedEl);
        charts.stacked.setOption(Object.assign({}, common, {
            title: { text: 'Presentes vs Ausentes' },
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            legend: { data: ['Presentes', 'Ausentes'] },
            xAxis: { type: 'category', data: ['Ing', 'Adm', 'Cont'] },
            yAxis: { type: 'value' },
            series: [
                { name: 'Presentes', type: 'bar', stack: 'total', data: [80, 60, 70] },
                { name: 'Ausentes', type: 'bar', stack: 'total', data: [20, 40, 30] }
            ]
        }));
    }

    // ============ Radar Chart =============
    let radarEl = document.getElementById('chart_radar');
    if (radarEl) {
        echarts.dispose(radarEl);
        charts.radar = echarts.init(radarEl);
        charts.radar.setOption(Object.assign({}, common, {
            title: { text: 'Radar Chart' },
            radar: {
                indicator: [
                    { name: 'Puntualidad', max: 100 },
                    { name: 'Participación', max: 100 },
                    { name: 'Regularidad', max: 100 },
                    { name: 'Conclusión', max: 100 }
                ]
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
        echarts.dispose(heatmapEl);
        charts.heatmap = echarts.init(heatmapEl);
        charts.heatmap.setOption(Object.assign({}, common, {
            title: { text: 'Heatmap Asistencia' },
            xAxis: { type: 'category', data: ['Lun','Mar','Mié','Jue','Vie'] },
            yAxis: { type: 'category', data: ['Mañana','Tarde'] },
            visualMap: { min: 0, max: 100, calculable: true },
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
        echarts.dispose(kpiEl);
        charts.kpi = echarts.init(kpiEl);
        charts.kpi.setOption(Object.assign({}, common, {
            title: { text: 'KPI: % Asistencia General' },
            series: [{
                type: 'gauge',
                progress: { show: true },
                detail: { valueAnimation: true, formatter: '{value}%' },
                data: [{ value: 76, name: 'Asistencia' }]
            }]
        }));
    }
}

// 🔑 Función pública para Livewire
window.paintCharts = () => {
    createCharts();
};

// 🔑 Redimensionar sin recrear todo
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        Object.values(charts).forEach(chart => {
            if (chart) chart.resize();
        });
    }, 200);
});
