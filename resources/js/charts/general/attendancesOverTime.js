// resources/js/charts/general/attendancesOverTime.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';

export function renderAttendancesOverTime(charts) {
  const el = document.getElementById('chart_attendances_time');
  if (!el) return;

  if (charts.attendancesOverTime) echarts.dispose(el);
  charts.attendancesOverTime = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch('/api/statistics/attendances-over-time')
    .then(res => res.json())
    .then(data => {
      // data: [{ date, count }, ...]
      const dates = data.map(i => i.date);
      const counts = data.map(i => i.count);

      charts.attendancesOverTime.setOption({
        ...common,
        title: {
          text: 'Asistencias vs Tiempo',
          left: 'center',
          textStyle: { color: dark ? '#fff' : '#333' }
        },
        tooltip: {
          trigger: 'axis',
          formatter: params => {
            // params puede ser un array si hay varias series
            const p = Array.isArray(params) ? params[0] : params;
            return `${p.axisValue}<br/>Asistencias: ${p.data}`;
          }
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
            name: 'Asistencias',
            type: 'line',
            smooth: true,
            data: counts,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 2 },
            itemStyle: {
              color: dark ? '#34d399' : '#059669' // verde
            },
            areaStyle: {
              color: dark ? 'rgba(52,211,153,0.06)' : 'rgba(5,150,105,0.06)'
            }
          }
        ]
      });
    })
    .catch(err => {
      console.error('Error cargando attendancesOverTime:', err);
    });
}
