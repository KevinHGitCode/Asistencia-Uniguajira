import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { chartsManager } from '../utils/chartsManager.js';

export function renderTopParticipants(charts) {
  const el = document.getElementById('chart_top_participants');
  if (!el) return;

  const chartId = 'topParticipants';

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

  fetch('/api/statistics/top-participants')
    .then(res => res.json())
    .then(data => {
      // Invertir el orden para que los que tienen más asistencias aparezcan primero (arriba)
      const reversedData = [...data].reverse();

      // Truncar nombres largos de forma más agresiva para ahorrar espacio
      const truncatedNames = reversedData.map(i => {
        const name = i.name || '';
        return name.length > 20 ? name.substring(0, 17) + '...' : name;
      });

      charts[chartId].setOption({
        ...common,
        title: {
          text: 'Participantes con más asistencias',
          textStyle: {
            color: dark ? '#fff' : '#333',
            fontSize: 15,
            fontWeight: '600'
          }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
          formatter: (params) => {
            const param = Array.isArray(params) ? params[0] : params;
            const originalName = reversedData[param.dataIndex]?.name || '';
            return `
              <div style="min-width: 180px">
                <strong>${originalName}</strong><br/>
                Asistencias: ${param.value}
              </div>
            `;
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
          splitLine: {
            lineStyle: {
              color: dark ? '#333' : '#f0f0f0',
              type: 'dashed'
            }
          }
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
          itemStyle: {
            color: dark ? '#f87171' : '#ef4444',
            borderRadius: [0, 4, 4, 0] // Esquinas redondeadas en el lado derecho
          },
          barWidth: 20,
          label: {
            show: true,
            position: 'right',
            color: dark ? '#fff' : '#333',
            fontSize: 11
          }
        }]
      });
    })
    .catch(err => {
      console.error('Error cargando top-participants:', err);
    });
}
