import { getEnhancedOptions, isDarkMode } from '../utils/theme.js';

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
      charts.programParticipantsPie.setOption({
        ...common,
        title: { text: 'Participantes por Programa', left: 'center', textStyle: { color: dark ? '#fff' : '#333' } },
        legend: {
          type: 'scroll',
          orient: 'vertical',
          right: 20,
          top: 50,
          bottom: 50,
          pageIconColor: '#3b82f6',
          pageTextStyle: { color: dark ? '#fff' : '#666' },
          textStyle: { color: dark ? '#fff' : '#333' },
          data: data.map(i => i.program)
        },
        series: [{
          type: 'pie',
          radius: ['35%', '60%'],
          center: ['40%', '50%'],
          avoidLabelOverlap: true,
          label: { color: dark ? '#fff' : '#333', formatter: '{b}\n{d}%', fontSize: 10 },
          labelLine: { length: 12, length2: 6 },
          labelLayout: { hideOverlap: true, moveOverlap: true },
          data: data.map(i => ({ value: i.count, name: i.program }))
        }]
      });
    });
}
