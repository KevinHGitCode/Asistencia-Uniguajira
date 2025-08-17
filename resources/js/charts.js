console.log('hola desde charts.js');
// // Initialize the echarts instance based on the prepared dom
// var myChart = echarts.init(document.getElementById('main'));

// // Specify the configuration items and data for the chart
// var option = {
//         title: {
//             text: 'ECharts Getting Started Example'
//         },
//         tooltip: {},
//         legend: {
//             data: ['sales']
//         },
//         xAxis: {
//             data: ['Shirts', 'Cardigans', 'Chiffons', 'Pants', 'Heels', 'Socks']
//         },
//         yAxis: {},
//         series: [
//             {
//             name: 'sales',
//             type: 'bar',
//             data: [5, 20, 36, 10, 10, 20]
//             }
//         ]
//     };

// // Display the chart using the configuration items and data just specified.
// myChart.setOption(option);

// Bar Chart
var chartBar = echarts.init(document.getElementById('chart_bar'));
chartBar.setOption({
    title: { text: 'Bar Chart' },
    xAxis: { data: ['A', 'B', 'C', 'D'] },
    yAxis: {},
    series: [{ type: 'bar', data: [5, 20, 36, 10] }]
});

// Line Chart
var chartLine = echarts.init(document.getElementById('chart_line'));
chartLine.setOption({
    title: { text: 'Line Chart' },
    xAxis: { type: 'category', data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'] },
    yAxis: { type: 'value' },
    series: [{ type: 'line', data: [150, 230, 224, 218, 135] }]
});

// Pie Chart
var chartPie = echarts.init(document.getElementById('chart_pie'));
chartPie.setOption({
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

// Radar Chart
var chartRadar = echarts.init(document.getElementById('chart_radar'));
chartRadar.setOption({
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