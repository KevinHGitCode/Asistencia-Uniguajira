// charts/general/programAttendancesBar.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { shortName } from '../utils/shortName.js';

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
      const abbreviated = data.map(i => ({
        short: shortName(i.program),
        full: i.program,
        count: i.count
      }));

      charts.programAttendancesBar.setOption({
        ...common,
        title: { 
          text: 'Asistencias por Programa', 
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
            const fullName = abbreviated[p.dataIndex]?.full ?? p.name;
            return `
              <div style="min-width:180px">
                <strong>${fullName}</strong><br>
                Asistencias: ${p.value}
              </div>
            `;
          }
        },
        grid: {
          left: 60,
          right: 20,
          bottom: 80, // deja espacio para nombres largos
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: abbreviated.map(i => i.short),
          axisLabel: { 
            color: dark ? '#fff' : '#333',
            rotate: 30, // gira las etiquetas para mayor legibilidad
            fontSize: 10,
            interval: 0 // muestra todos los nombres
          },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } }
        },
        yAxis: {
          type: 'value',
          axisLabel: { color: dark ? '#fff' : '#333' },
          axisLine: { lineStyle: { color: dark ? '#555' : '#ddd' } },
          splitLine: { lineStyle: { color: dark ? '#333' : '#f0f0f0' } }
        },
        series: [{
          type: 'bar',
          data: abbreviated.map(i => i.count),
          itemStyle: {
            color: dark ? '#3b82f6' : '#60a5fa' // azul Tailwind
          },
          barMaxWidth: 25
        }]
      });
    });
}
