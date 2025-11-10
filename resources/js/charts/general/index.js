// resources/js/charts/general/index.js
import { chartsManager } from '../utils/chartsManager.js';
import { renderProgramAttendancesBar } from './programAttendancesBar.js';
import { renderProgramParticipantsPie } from './programParticipantsPie.js';
import { renderTopEvents } from './topEvents.js';
import { renderTopParticipants } from './topParticipants.js';
import { renderTopUsers } from './topUsers.js';
import { renderEventsByRole } from './eventsByRole.js';
import { renderEventsByUser } from './eventsByUser.js';
import { renderEventsOverTime } from './eventsOverTime.js';
import { renderAttendancesOverTime } from './attendancesOverTime.js';
import { initStatisticsCounters, cleanupStatisticsCounters } from './statisticsCounters.js';
import { updateFilters } from './filtersManager.js';

// Objeto para mantener las referencias de los gráficos
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

/**
 * Renderiza todos los gráficos
 */
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

/**
 * Inicializa el sistema de gráficos
 */
function initChartsSystem() {
  // Inicializar listeners globales (resize, theme, cleanup)
  chartsManager.initGlobalListeners();

  // Inicializar contadores de estadísticas
  initStatisticsCounters();

  // Renderizar todos los gráficos
  renderAllCharts();

  console.log('✓ Sistema de gráficos inicializado');
}

/**
 * Limpia el sistema de gráficos
 */
function cleanupChartsSystem() {
  // Limpiar contadores
  cleanupStatisticsCounters();

  // Limpiar manager de gráficos
  chartsManager.cleanup();

  console.log('✓ Sistema de gráficos limpiado');
}

/**
 * Función principal exportada globalmente
 */
window.createGeneralCharts = () => {
  // Limpiar sistema anterior si existe
  cleanupChartsSystem();

  // Inicializar nuevo sistema
  initChartsSystem();

  // Escuchar cambios de filtros
  window.addEventListener('statistics-filters-changed', (event) => {
    const { filters } = event.detail;
    updateFilters(filters);

    // Recargar todos los gráficos con los nuevos filtros
    renderAllCharts();

    // Recargar contadores
    initStatisticsCounters();
  });

  // Escuchar cambios de tema para recargar gráficos
  window.addEventListener('charts-theme-changed', () => {
    renderAllCharts();
  });
};

// Exponer función de cleanup globalmente
window.cleanupGeneralCharts = cleanupChartsSystem;

console.log('✓ Módulo charts/general cargado');
