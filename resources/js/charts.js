console.log('hola desde charts.js');

// Guardamos las instancias globalmente
let charts = {
    bar: null,
    line: null,
    pie: null,
    radar: null
};

function createCharts() {
    // Bar Chart
    let barEl = document.getElementById('chart_bar');
    if (barEl) {
        echarts.dispose(barEl);
        charts.bar = echarts.init(barEl);
        charts.bar.setOption({
            title: { text: 'Bar Chart' },
            xAxis: { data: ['A', 'B', 'C', 'D'] },
            yAxis: {},
            series: [{ type: 'bar', data: [5, 20, 36, 10] }]
        });
    }

    // Line Chart
    let lineEl = document.getElementById('chart_line');
    if (lineEl) {
        echarts.dispose(lineEl);
        charts.line = echarts.init(lineEl);
        charts.line.setOption({
            title: { text: 'Line Chart' },
            xAxis: { type: 'category', data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'] },
            yAxis: { type: 'value' },
            series: [{ type: 'line', data: [150, 230, 224, 218, 135] }]
        });
    }

    // Pie Chart
    let pieEl = document.getElementById('chart_pie');
    if (pieEl) {
        echarts.dispose(pieEl);
        charts.pie = echarts.init(pieEl);
        charts.pie.setOption({
            title: { text: 'Pie Chart', left: 'center' },
            series: [{
                type: 'pie',
                radius: '50%',
                data: [
                    { value: 1048, name: 'A' },
                    { value: 735, name: 'B' },
                    { value: 580, name: 'C' }
                ]
            }]
        });
    }

    // Radar Chart
    let radarEl = document.getElementById('chart_radar');
    if (radarEl) {
        echarts.dispose(radarEl);
        charts.radar = echarts.init(radarEl);
        charts.radar.setOption({
            title: { text: 'Radar Chart' },
            radar: {
                indicator: [
                    { name: 'Math', max: 100 },
                    { name: 'English', max: 100 },
                    { name: 'Science', max: 100 },
                    { name: 'PE', max: 100 }
                ]
            },
            series: [{
                type: 'radar',
                data: [{ value: [90, 70, 85, 60], name: 'Student' }]
            }]
        });
    }
}

// 🔑 Función pública para Livewire
window.paintCharts = () => {
    createCharts();
};

// 🔑 Redimensionar sin recrear todo
window.addEventListener('resize', () => {
    Object.values(charts).forEach(chart => {
        if (chart) chart.resize();
    });
});
