// resources/js/charts/general/eventsOverTime.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';

export function renderEventsOverTime(charts) {
  const el = document.getElementById('chart_events_time');
  if (!el) return;

  if (charts.eventsOverTime) echarts.dispose(el);
  charts.eventsOverTime = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch('/api/statistics/events-over-time')
    .then(res => res.json())
    .then(data => {
      // Asegúrate que data sea un array con { date, count }
      const dates = data.map(i => i.date);
      const counts = data.map(i => i.count);

      charts.eventsOverTime.setOption({
        ...common,
        title: {
          text: 'Eventos vs Tiempo',
          left: 'center',
          textStyle: { color: dark ? '#fff' : '#333' }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' }
        },
        xAxis: {
          type: 'category',
          data: dates,
          axisLabel: { color: dark ? '#fff' : '#333', rotate: 0, interval: 0 },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } }
        },
        yAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: { lineStyle: { color: dark ? '#333' : '#f0f0f0' } }
        },
        grid: {
          left: '6%',
          right: '6%',
          bottom: '10%',
          containLabel: true
        },
        series: [
          {
            name: 'Eventos',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            data: counts,
            lineStyle: { width: 2 },
            itemStyle: {
              color: dark ? '#60a5fa' : '#2563eb' // azul suave en dark, azul fuerte en light
            },
            areaStyle: {
              color: (dark) ? 'rgba(96,165,250,0.06)' : 'rgba(37,99,235,0.06)'
            }
          }
        ]
      });
    })
    .catch(err => {
      // opcional: limpiar gráfico o mostrar mensaje
      console.error('Error cargando eventsOverTime:', err);
    });
}
