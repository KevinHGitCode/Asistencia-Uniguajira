import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';

export function renderProgramAttendancesBar(charts) {
  const el = document.getElementById('chart_program_attendances_bar');
  if (!el) return;

  if (charts.programAttendancesBar) echarts.dispose(el);
  charts.programAttendancesBar = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch('/api/statistics/attendances-by-program')
    .then(res => res.json())
    .then(data => {
      charts.programAttendancesBar.setOption({
        ...common,
        title: { text: 'Asistencias por Programa', textStyle: { color: dark ? '#fff' : '#333' } },
        xAxis: {
          type: 'category',
          data: data.map(i => i.program),
          axisLabel: { color: dark ? '#fff' : '#333' },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } }
        },
        yAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: { lineStyle: { color: dark ? '#333' : '#f0f0f0' } }
        },
        series: [{ type: 'bar', data: data.map(i => i.count) }]
      });
    });
}
