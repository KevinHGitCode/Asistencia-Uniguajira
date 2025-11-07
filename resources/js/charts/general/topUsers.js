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
      charts.topUsers.setOption({
        ...common,
        title: { text: 'Usuarios con más asistencias', textStyle: { color: dark ? '#fff' : '#333' } },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          textStyle: { color: dark ? '#fff' : '#333' },
        },
        grid: { left: 80, right: 20, bottom: 50, containLabel: true },
        xAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          splitLine: { lineStyle: { color: dark ? '#333' : '#f0f0f0' } },
        },
        yAxis: {
          type: 'category',
          data: data.map(i => i.name),
          axisLabel: { color: dark ? '#fff' : '#333', fontSize: 10 },
        },
        series: [{
          type: 'bar',
          data: data.map(i => i.count),
          itemStyle: { color: dark ? '#34d399' : '#10b981' },
          barWidth: 20,
        }]
      });
    });
}
