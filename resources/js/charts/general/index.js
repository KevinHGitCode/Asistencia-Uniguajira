import { renderProgramAttendancesBar } from './programAttendancesBar.js';
import { renderProgramParticipantsPie } from './programParticipantsPie.js';
import { renderTopEvents } from './topEvents.js';
import { renderTopParticipants } from './topParticipants.js';
import { renderTopUsers } from './topUsers.js';
import { renderEventsByRole } from './eventsByRole.js';
import { renderEventsByUser } from './eventsByUser.js';
import { renderEventsOverTime } from './eventsOverTime.js';
import { renderAttendancesOverTime } from './attendancesOverTime.js';
import { initStatisticsCounters } from './statisticsCounters.js';
import { updateFilters } from './filtersManager.js';

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
  renderEventsOverTime(charts);
  renderAttendancesOverTime(charts);
  renderTopEvents(charts);
  renderTopParticipants(charts);
  renderTopUsers(charts);
  renderEventsByRole(charts);
  renderEventsByUser(charts);

  // Escuchar cambios de filtros y recargar gráficos
  window.addEventListener('statistics-filters-changed', (event) => {
    const { filters } = event.detail;
    updateFilters(filters);
    
    // Recargar todos los gráficos con los nuevos filtros
    renderProgramAttendancesBar(charts);
    renderProgramParticipantsPie(charts);
    renderEventsOverTime(charts);
    renderAttendancesOverTime(charts);
    renderTopEvents(charts);
    renderTopParticipants(charts);
    renderTopUsers(charts);
    renderEventsByRole(charts);
    renderEventsByUser(charts);
    
    // Recargar contadores
    initStatisticsCounters();
  });

};

window.createGeneralCharts = createGeneralCharts;

console.log('Cargado charts/index.js');