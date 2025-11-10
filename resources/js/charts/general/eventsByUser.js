// charts/general/eventsByUser.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { getApiUrl } from './filtersManager.js';
import { chartsManager } from '../utils/chartsManager.js';

export function renderEventsByUser(charts) {
  const el = document.getElementById('chart_events_by_user');
  if (!el) return;

  const chartId = 'eventsByUser';

  // Limpiar instancia anterior si existe
  if (charts[chartId]) {
    echarts.dispose(el);
    chartsManager.dispose(chartId);
  }

  // Crear nueva instancia
  charts[chartId] = echarts.init(el);

  // Registrar en el manager
  chartsManager.register(chartId, charts[chartId]);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch(getApiUrl('/api/statistics/events-by-user'))
    .then(res => res.json())
    .then(data => {
      charts[chartId].setOption({
        ...common,
        title: {
          text: 'Eventos por Usuario',
          textStyle: { color: dark ? '#fff' : '#333' }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
          formatter: params => {
            const p = params[0];
            return `
              <div style="min-width:180px">
                <strong>${p.name}</strong><br>
                Eventos: ${p.value}
              </div>
            `;
          }
        },
        grid: {
          left: 60,
          right: 20,
          bottom: 80,
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: data.map(i => i.name),
          axisLabel: {
            color: dark ? '#fff' : '#333',
            rotate: 30,
            fontSize: 10,
            interval: 0
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } }
        },
        yAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: {
            lineStyle: {
              color: dark ? '#333' : '#f0f0f0',
              type: 'dashed'
            }
          }
        },
        series: [{
          type: 'bar',
          data: data.map(i => i.count),
          itemStyle: {
            color: dark ? '#3b82f6' : '#60a5fa',
            borderRadius: [4, 4, 0, 0] // Esquinas redondeadas en la parte superior
          },
          barMaxWidth: 25
        }]
      });
    })
    .catch(err => {
      console.error('Error cargando events-by-user:', err);
    });
}
