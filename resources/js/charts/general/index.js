// resources/js/charts/general/index.js
import { chartsManager } from '../utils/chartsManager.js';
import { renderProgramAttendancesBar } from './programAttendancesBar.js';
import { renderProgramParticipantsPie } from './programParticipantsPie.js';
import { renderTopEvents } from './topEvents.js';
import { renderTopParticipants } from './topParticipants.js';
import { renderTopUsers } from './topUsers.js';
import { renderEventsByRole } from './eventsByRole.js';
import { renderEventsByUser } from './eventsByUser.js';
// import { renderEventsOverTime } from './eventsOverTime.js';
// import { renderAttendancesOverTime } from './attendancesOverTime.js';
import { initStatisticsCounters, cleanupStatisticsCounters } from './statisticsCounters.js';
import { updateFilters } from './filtersManager.js';

const charts = {
  programAttendancesBar: null,
  programParticipantsPie: null,
  eventsOverTime: null,
  attendancesOverTime: null,
  topEvents: null,
  topParticipants: null,
  topUsers: null,
  eventsByRole: null,
  eventsByUser: null
};

function renderAllCharts() {
  renderProgramAttendancesBar(charts);
  renderProgramParticipantsPie(charts);
//   renderEventsOverTime(charts);
//   renderAttendancesOverTime(charts);
  renderTopEvents(charts);
  renderTopParticipants(charts);
  renderTopUsers(charts);
  renderEventsByRole(charts);
  renderEventsByUser(charts);
}

function initChartsSystem() {
  chartsManager.initGlobalListeners();
  initStatisticsCounters();
  renderAllCharts();
  console.log('✓ Sistema de gráficos inicializado');
}

function cleanupChartsSystem() {
  cleanupStatisticsCounters();
  chartsManager.cleanup();
  console.log('✓ Sistema de gráficos limpiado');
}

window.createGeneralCharts = () => {
  cleanupChartsSystem();

  // Esperar un tick para asegurar que el DOM está actualizado
  requestAnimationFrame(() => {
    initChartsSystem();
  });

  window.addEventListener('statistics-filters-changed', (event) => {
    const { filters } = event.detail;
    updateFilters(filters);
    renderAllCharts();
    initStatisticsCounters();
  });

  window.addEventListener('charts-theme-changed', () => {
    renderAllCharts();
  });
};

window.cleanupGeneralCharts = cleanupChartsSystem;

console.log('✓ Módulo charts/general cargado');
