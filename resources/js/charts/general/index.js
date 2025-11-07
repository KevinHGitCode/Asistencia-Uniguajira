import { renderProgramAttendancesBar } from './programAttendancesBar.js';
import { renderProgramParticipantsPie } from './programParticipantsPie.js';
import { renderTopEvents } from './topEvents.js';
import { renderTopParticipants } from './topParticipants.js';
import { renderTopUsers } from './topUsers.js';
import { renderEventsByRole } from './eventsByRole.js';
import { renderEventsByUser } from './eventsByUser.js';
import { initStatisticsCounters } from './statisticsCounters.js';
// import { renderEventsOverTime } from './eventsOverTime.js';
// import { renderAttendancesOverTime } from './attendancesOverTime.js';

window.createGeneralCharts = () => {
  const charts = {
    programAttendancesBar: null,
    programParticipantsPie: null,
    eventsOverTime: null,
    attendancesOverTime: null,
    eventsByRole: null,
    eventsByUser: null
  };

  // Inicializar contadores de estadísticas
  initStatisticsCounters();

  // Renderizar gráficas
  renderProgramAttendancesBar(charts);
  renderProgramParticipantsPie(charts);
  renderTopEvents(charts);
  renderTopParticipants(charts);
  renderTopUsers(charts);
  renderEventsByRole(charts);
  renderEventsByUser(charts);

  
  // renderEventsOverTime(charts);
  // renderAttendancesOverTime(charts);
};

window.createGeneralCharts = createGeneralCharts;

console.log('Cargado charts/index.js');