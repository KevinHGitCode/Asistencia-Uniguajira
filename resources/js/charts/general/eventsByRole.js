// charts/general/eventsByRole.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { getApiUrl } from './filtersManager.js';

export function renderEventsByRole(charts) {
  const el = document.getElementById('chart_events_by_role');
  if (!el) return;

  if (charts.eventsByRole) echarts.dispose(el);
  charts.eventsByRole = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch(getApiUrl('/api/statistics/events-by-role'))
    .then(res => res.json())
    .then(data => {
      // Calcular el total y los porcentajes
      const total = data.reduce((sum, i) => sum + i.count, 0);
      const grouped = [];
      let smallSum = 0;

      for (const i of data) {
        const percent = (i.count / total) * 100;
        // Agrupar roles con menos del 3% en "Otros"
        if (percent < 3) {
          smallSum += i.count;
        } else {
          grouped.push(i);
        }
      }

      // Agregar grupo "Otros" si aplica
      if (smallSum > 0) {
        grouped.push({ role: 'Otros', count: smallSum, isOther: true });
      }

      charts.eventsByRole.setOption({
        ...common,
        title: { 
          text: 'Eventos por Rol', 
          left: 'center', 
          textStyle: { color: dark ? '#fff' : '#333' } 
        },
        tooltip: {
          trigger: 'item',
          formatter: params => {
            return `
              <div style="min-width:180px">
                <strong>${params.name}</strong><br>
                Eventos: ${params.value}<br>
                Porcentaje: ${params.percent}%
              </div>
            `;
          },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
        },
        legend: {
          type: 'scroll',
          orient: 'vertical',
          right: 20,
          top: 50,
          bottom: 50,
          pageIconColor: '#3b82f6',
          pageTextStyle: { color: dark ? '#fff' : '#666' },
          textStyle: { color: dark ? '#fff' : '#333' },
          data: grouped.map(i => i.role)
        },
        series: [{
          type: 'pie',
          radius: ['0%', '55%'],
          center: ['40%', '50%'],
          avoidLabelOverlap: true,
          label: { 
            color: dark ? '#fff' : '#333', 
            formatter: '{b} - {d}%', 
            fontSize: 10 
          },
          labelLine: { length: 14, length2: 10 },
          labelLayout: { hideOverlap: true, moveOverlap: true },
          data: grouped.map(i => ({
            value: i.count,
            name: i.role,
            itemStyle: i.isOther ? { color: '#9ca3af' } : undefined
          }))
        }]
      });
    });
}

