// resources/js/charts/general/eventsOverTime.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { getApiUrl } from './filtersManager.js';

export function renderEventsOverTime(charts) {
  const el = document.getElementById('chart_events_time');
  if (!el) return;

  if (charts.eventsOverTime) echarts.dispose(el);
  charts.eventsOverTime = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch(getApiUrl('/api/statistics/events-over-time'))
    .then(res => res.json())
    .then(data => {
      if (!data || data.length === 0) {
        console.warn('No hay datos para eventsOverTime');
        return;
      }

      // Formatear fechas a formato corto (DD/MM)
      const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${day}/${month}`;
      };

      const dates = data.map(i => i.date);
      const counts = data.map(i => i.count);
      const formattedDates = dates.map(formatDate);
      
      // Calcular intervalo automático basado en cantidad de datos
      const dataLength = dates.length;
      let interval = 0;
      let rotate = 0;
      let bottomSpace = '15%';
      
      if (dataLength > 30) {
        interval = Math.floor(dataLength / 15); // Mostrar ~15 etiquetas máximo
        rotate = 45;
        bottomSpace = '20%';
      } else if (dataLength > 15) {
        interval = Math.floor(dataLength / 10);
        rotate = 30;
        bottomSpace = '18%';
      } else if (dataLength > 7) {
        interval = 0;
        rotate = 15;
        bottomSpace = '15%';
      }

      charts.eventsOverTime.setOption({
        ...common,
        title: {
          text: 'Eventos vs Tiempo',
          left: 'center',
          top: 10,
          textStyle: { color: dark ? '#fff' : '#333', fontSize: 16, fontWeight: '600' }
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'line' },
          backgroundColor: dark ? 'rgba(50,50,50,0.85)' : 'rgba(255,255,255,0.95)',
          borderColor: dark ? '#555' : '#ddd',
          textStyle: { color: dark ? '#fff' : '#333' },
          formatter: (params) => {
            const p = Array.isArray(params) ? params[0] : params;
            const originalDate = dates[p.dataIndex] || p.axisValue;
            return `
              <div style="padding: 4px 0;">
                <div style="font-weight: 600; margin-bottom: 4px;">${originalDate}</div>
                <div style="color: ${dark ? '#60a5fa' : '#2563eb'};">
                  <span style="font-size: 18px; font-weight: 700;">${p.data}</span>
                  <span style="font-size: 12px; margin-left: 4px;">eventos</span>
                </div>
              </div>
            `;
          }
        },
        xAxis: {
          type: 'category',
          data: formattedDates,
          axisLabel: {
            color: dark ? '#fff' : '#333',
            rotate: rotate,
            interval: interval,
            fontSize: 10,
            margin: 12
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          axisTick: { show: true, alignWithLabel: true }
        },
        yAxis: {
          type: 'value',
          minInterval: 1, // Solo valores enteros
          axisLabel: {
            color: dark ? '#fff' : '#333',
            fontSize: 11,
            formatter: (value) => {
              // Asegurar que solo muestre enteros
              return Math.floor(value).toString();
            }
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: {
            lineStyle: {
              color: dark ? '#333' : '#f0f0f0',
              type: 'dashed',
              width: 1
            }
          },
          name: 'Eventos',
          nameTextStyle: {
            color: dark ? '#9ca3af' : '#6b7280',
            fontSize: 12
          }
        },
        grid: {
          left: '8%',
          right: '8%',
          top: '18%',
          bottom: bottomSpace,
          containLabel: false
        },
        dataZoom: [
          {
            type: 'inside',
            start: 0,
            end: 100
          },
          {
            type: 'slider',
            start: 0,
            end: 100,
            height: 20,
            bottom: 10,
            handleIcon: 'path://M30.9,53.2C16.8,53.2,5.3,41.7,5.3,27.6S16.8,2,30.9,2C45,2,56.4,13.5,56.4,27.6S45,53.2,30.9,53.2z M30.9,3.5C17.6,3.5,6.8,14.4,6.8,27.6c0,13.2,10.8,24.1,24.1,24.1C44.2,51.7,55,40.8,55,27.6C54.9,14.4,44.1,3.5,30.9,3.5z M36.9,35.8c0,0.6-0.4,1-1,1H26c-0.6,0-1-0.4-1-1V19.4c0-0.6,0.4-1,1-1h9.9c0.6,0,1,0.4,1,1V35.8z',
            handleSize: '80%',
            handleStyle: {
              color: dark ? '#60a5fa' : '#2563eb',
              borderColor: dark ? '#3b82f6' : '#1d4ed8'
            },
            textStyle: { color: dark ? '#fff' : '#333' }
          }
        ],
        series: [
          {
            name: 'Eventos',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 8,
            data: counts,
            lineStyle: {
              width: 3,
              color: dark ? '#60a5fa' : '#2563eb'
            },
            itemStyle: {
              color: dark ? '#60a5fa' : '#2563eb',
              borderWidth: 2,
              borderColor: dark ? '#1f2937' : '#ffffff'
            },
            areaStyle: {
              color: dark ? 'rgba(96,165,250,0.1)' : 'rgba(37,99,235,0.08)'
            },
            emphasis: {
              focus: 'series',
              itemStyle: {
                borderWidth: 3,
                shadowBlur: 10,
                shadowColor: dark ? 'rgba(96,165,250,0.5)' : 'rgba(37,99,235,0.3)'
              }
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
