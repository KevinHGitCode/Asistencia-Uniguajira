// charts/general/programParticipantsPie.js
import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';
import { shortName } from '../utils/shortName.js';

export function renderProgramParticipantsPie(charts) {
  const el = document.getElementById('chart_program_participants_pie');
  if (!el) return;

  if (charts.programParticipantsPie) echarts.dispose(el);
  charts.programParticipantsPie = echarts.init(el);

  const common = getEnhancedOptions();
  const dark = isDarkMode();

  fetch('/api/statistics/participants-by-program')
    .then(res => res.json())
    .then(data => {
      // 🔹 Calcular el total y los porcentajes
      const total = data.reduce((sum, i) => sum + i.count, 0);
      const grouped = [];
      let smallSum = 0;

      for (const i of data) {
        const percent = (i.count / total) * 100;
        // Agrupar programas con menos del 3% en "Otros"
        if (percent < 3) {
          smallSum += i.count;
        } else {
          grouped.push(i);
        }
      }

      // 🔹 Agregar grupo "Otros" si aplica
      if (smallSum > 0) {
        grouped.push({ program: 'Otros', count: smallSum, isOther: true });
      }

      charts.programParticipantsPie.setOption({
        ...common,
        title: { 
          text: 'Participantes por Programa', 
          left: 'center', 
          textStyle: { color: dark ? '#fff' : '#333' } 
        },
        tooltip: {
          trigger: 'item',
          formatter: params => {
            const fullItem = data.find(i => shortName(i.program) === params.name);
            const fullName = fullItem ? fullItem.program : params.name;
            return `
              <div style="min-width:180px">
                <strong>${fullName}</strong><br>
                Participantes: ${params.value}<br>
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
          data: grouped.map(i => shortName(i.program))
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
            name: shortName(i.program),
            fullName: i.program,
            // 🎨 Forzar color gris si es "Otros"
            itemStyle: i.isOther ? { color: '#9ca3af' } : undefined
          }))
        }]
      });
    });
}
