// Configuración de gráficas específicas del evento
let eventCharts = {
    programPie: null,
    programBar: null,
    rolePie: null,
    roleBar: null
};

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

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
        }
    };
}

window.createEventCharts = (eventId) => {
    const common = getCommonOptions();

    // Gráfica circular - Programa
    let programPieEl = document.getElementById('chart_program_pie');
    if (programPieEl) {
        if (eventCharts.programPie) {
            echarts.dispose(programPieEl);
        }
        eventCharts.programPie = echarts.init(programPieEl);
        fetch(`/api/statistics/event/${eventId}/programs`)
            .then(response => response.json())
            .then(data => {
                eventCharts.programPie.setOption(Object.assign({}, common, {
                    title: {
                        text: 'Distribución por Programa',
                        left: 'center'
                    },
                    series: [{
                        type: 'pie',
                        radius: '50%',
                        data: data.map(item => ({ value: item.count, name: item.program }))
                    }]
                }));
            });
    }

    // Gráfica de barras - Programa
    let programBarEl = document.getElementById('chart_program_bar');
    if (programBarEl) {
        if (eventCharts.programBar) {
            echarts.dispose(programBarEl);
        }
        eventCharts.programBar = echarts.init(programBarEl);
        fetch(`/api/statistics/event/${eventId}/programs`)
            .then(response => response.json())
            .then(data => {
                eventCharts.programBar.setOption(Object.assign({}, common, {
                    title: {
                        text: 'Participación por Programa'
                    },
                    xAxis: {
                        type: 'category',
                        data: data.map(item => item.program)
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        type: 'bar',
                        data: data.map(item => item.count)
                    }]
                }));
            });
    }

    // Gráfica circular - Rol
    let rolePieEl = document.getElementById('chart_role_pie');
    if (rolePieEl) {
        if (eventCharts.rolePie) {
            echarts.dispose(rolePieEl);
        }
        eventCharts.rolePie = echarts.init(rolePieEl);
        fetch(`/api/statistics/event/${eventId}/roles`)
            .then(response => response.json())
            .then(data => {
                eventCharts.rolePie.setOption(Object.assign({}, common, {
                    title: {
                        text: 'Distribución por Rol',
                        left: 'center'
                    },
                    series: [{
                        type: 'pie',
                        radius: '50%',
                        data: data.map(item => ({ value: item.count, name: item.role }))
                    }]
                }));
            });
    }

    // Gráfica de barras - Rol
    let roleBarEl = document.getElementById('chart_role_bar');
    if (roleBarEl) {
        if (eventCharts.roleBar) {
            echarts.dispose(roleBarEl);
        }
        eventCharts.roleBar = echarts.init(roleBarEl);
        fetch(`/api/statistics/event/${eventId}/roles`)
            .then(response => response.json())
            .then(data => {
                eventCharts.roleBar.setOption(Object.assign({}, common, {
                    title: {
                        text: 'Participación por Rol'
                    },
                    xAxis: {
                        type: 'category',
                        data: data.map(item => item.role)
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        type: 'bar',
                        data: data.map(item => item.count)
                    }]
                }));
            });
    }
}
