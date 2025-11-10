// resources/js/charts/general/eventsByRole.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { getApiUrl } from './filtersManager.js';
import { chartsManager } from '../utils/chartsManager.js';

export function renderEventsByRole(charts) {
  const el = document.getElementById('chart_events_by_role');
  if (!el) return;

  const chartId = 'eventsByRole';

  // Limpiar instancia anterior
  if (charts[chartId]) {
    echarts.dispose(el);
    chartsManager.dispose(chartId);
  }

  // Crear y registrar nueva instancia
  charts[chartId] = echarts.init(el);
  chartsManager.register(chartId, charts[chartId]);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch(getApiUrl('/api/statistics/events-by-role'))
    .then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    })
    .then(data => {
      if (!data || data.length === 0) {
        console.warn('No hay datos para eventsByRole');
        charts[chartId].setOption({
          title: {
            text: 'Sin datos disponibles',
            left: 'center',
            top: 'middle',
            textStyle: { color: dark ? '#9ca3af' : '#6b7280', fontSize: 14 }
          }
        });
        return;
      }

      charts[chartId].setOption({
        ...common,
        title: {
          text: 'Eventos por Rol',
          left: 'center',
          top: 10,
          textStyle: {
            color: dark ? '#fff' : '#333',
            fontSize: 16,
            fontWeight: '600'
          }
        },
        tooltip: {
          trigger: 'item',
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
          formatter: params => {
            return `
              <div style="padding: 4px 0;">
                <div style="font-weight: 600; margin-bottom: 4px;">${params.name}</div>
                <div style="color: ${params.color};">
                  <span style="font-size: 18px; font-weight: 700;">${params.value}</span>
                  <span style="font-size: 12px; margin-left: 4px;">eventos</span>
                </div>
                <div style="color: ${dark ? '#9ca3af' : '#6b7280'}; font-size: 11px; margin-top: 2px;">
                  ${params.percent.toFixed(1)}% del total
                </div>
              </div>
            `;
          }
        },
        legend: {
          type: 'scroll',
          orient: 'vertical',
          right: 20,
          top: 60,
          bottom: 50,
          pageIconColor: '#3b82f6',
          pageIconInactiveColor: dark ? '#4b5563' : '#d1d5db',
          pageTextStyle: { color: dark ? '#fff' : '#666' },
          textStyle: {
            color: dark ? '#fff' : '#333',
            fontSize: 11
          },
          itemWidth: 14,
          itemHeight: 14,
          data: data.map(i => i.role)
        },
        series: [{
          type: 'pie',
          radius: ['35%', '65%'],
          center: ['40%', '50%'],
          avoidLabelOverlap: true,
          label: {
            color: dark ? '#fff' : '#333',
            formatter: '{b}\n{d}%',
            fontSize: 10,
            fontWeight: '500'
          },
          labelLine: {
            length: 18,
            length2: 10,
            lineStyle: {
              color: dark ? '#4b5563' : '#d1d5db'
            }
          },
          labelLayout: {
            hideOverlap: true,
            moveOverlap: true
          },
          emphasis: {
            label: {
              show: true,
              fontSize: 12,
              fontWeight: 'bold'
            },
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          },
          data: data.map(i => ({
            value: i.count,
            name: i.role,
            itemStyle: {
              // Esquinas redondeadas para todas las porciones
              borderRadius: 6,
              borderColor: dark ? '#1f2937' : '#ffffff',
              borderWidth: 2
            }
          }))
        }]
      });
    })
    .catch(err => {
      console.error('Error cargando eventsByRole:', err);
      charts[chartId].setOption({
        title: {
          text: 'Error al cargar datos',
          left: 'center',
          top: 'middle',
          textStyle: { color: '#ef4444', fontSize: 14 }
        }
      });
    });
}
