import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';

export function renderTopUsers(charts) {
  const el = document.getElementById('chart_top_users');
  if (!el) return;

  if (charts.topUsers) echarts.dispose(el);
  charts.topUsers = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch('/api/statistics/top-users')
    .then(res => res.json())
    .then(data => {
      // Invertir el orden para que los que tienen más asistencias aparezcan primero (arriba)
      const reversedData = [...data].reverse();
      
      // Truncar nombres largos de forma más agresiva para ahorrar espacio
      const truncatedNames = reversedData.map(i => {
        const name = i.name || '';
        return name.length > 20 ? name.substring(0, 17) + '...' : name;
      });

      charts.topUsers.setOption({
        ...common,
        title: {
          text: 'Usuarios con más asistencias',
          textStyle: { color: dark ? '#fff' : '#333' }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          textStyle: { color: dark ? '#fff' : '#333' },
          formatter: (params) => {
            const param = Array.isArray(params) ? params[0] : params;
            const originalName = reversedData[param.dataIndex]?.name || '';
            return `${originalName}<br/>Asistencias: ${param.value}`;
          }
        },
        grid: {
          left: 120,
          right: 50,
          top: 50,
          bottom: 40,
          containLabel: false
        },
        xAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          splitLine: { lineStyle: { color: dark ? '#333' : '#f0f0f0' } }
        },
        yAxis: {
          type: 'category',
          data: truncatedNames,
          axisLabel: {
            color: dark ? '#fff' : '#333',
            fontSize: 11,
            width: 110,
            overflow: 'truncate',
            ellipsis: '...'
          }
        },
        series: [{
          type: 'bar',
          data: reversedData.map(i => i.count),
          itemStyle: { color: dark ? '#34d399' : '#10b981' },
          barWidth: 20,
          label: {
            show: true,
            position: 'right',
            color: dark ? '#fff' : '#333',
            fontSize: 11
          }
        }]
      });
    });
}
