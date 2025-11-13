// resources/js/charts/general/programAttendancesBar.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { shortName } from '../utils/shortName.js';
import { getApiUrl } from './filtersManager.js';
import { chartsManager } from '../utils/chartsManager.js';

export function renderProgramAttendancesBar(charts) {
  const el = document.getElementById('chart_program_attendances_bar');
  if (!el) return;

  const chartId = 'programAttendancesBar';

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

  fetch(getApiUrl('/api/statistics/attendances-by-program'))
    .then(res => res.json())
    .then(data => {
      const abbreviated = data.map(i => ({
        short: shortName(i.program),
        full: i.program,
        count: i.count
      }));

      charts[chartId].setOption({
        ...common,
        title: {
          text: 'Asistencias por Programa',
          left: 'center',
          top: 10,
          textStyle: {
            color: dark ? '#fff' : '#333',
            fontSize: 16,
            fontWeight: '600'
          }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
            formatter: params => {
                const p = params[0];
                const fullName = abbreviated[p.dataIndex]?.full ?? p.name;
                return `
                    <div style="padding: 4px 0; width: auto; max-width: 320px;">
                        <div style="font-weight: 600; margin-bottom: 4px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.2;">
                            ${fullName}
                        </div>
                        <div style="color: ${dark ? '#60a5fa' : '#2563eb'};">
                            <span style="font-size: 16px; font-weight: 500;">${p.value} asistencias</span>
                        </div>
                    </div>
                `;
            }
        },
        grid: {
          left: '8%',
          right: '8%',
          top: '18%',
          bottom: '22%',
          containLabel: false
        },
        xAxis: {
          type: 'category',
          data: abbreviated.map(i => i.short),
          axisLabel: {
            color: dark ? '#fff' : '#333',
            rotate: 30,
            fontSize: 10,
            interval: 0,
            margin: 12
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          axisTick: { show: true, alignWithLabel: true }
        },
        yAxis: {
          type: 'value',
          minInterval: 1,
          axisLabel: {
            color: dark ? '#fff' : '#333',
            fontSize: 11,
            formatter: (value) => Math.floor(value).toString()
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: {
            lineStyle: {
              color: dark ? '#333' : '#f0f0f0',
              type: 'dashed'
            }
          },
          name: 'Asistencias',
          nameTextStyle: {
            color: dark ? '#9ca3af' : '#6b7280',
            fontSize: 12
          }
        },
        series: [{
          type: 'bar',
          data: abbreviated.map(i => i.count),
          itemStyle: {
            color: dark ? '#3b82f6' : '#60a5fa',
            borderRadius: [2, 2, 0, 0]
          },
          barMaxWidth: 40,
          emphasis: {
            itemStyle: {
              color: dark ? '#60a5fa' : '#3b82f6',
              shadowBlur: 10,
              shadowColor: dark ? 'rgba(59, 130, 246, 0.5)' : 'rgba(96, 165, 250, 0.3)'
            }
          }
        }]
      });
    })
    .catch(err => {
      console.error('Error cargando attendances-by-program:', err);
    });
}
