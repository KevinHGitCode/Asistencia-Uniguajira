import React, { useEffect } from 'react';

import { useTheme }         from './hooks/useTheme.js';
import { useUsuariosStats } from './hooks/useUsuariosStats.js';
import { useFilters }       from './hooks/useFilters.js';

import { FiltersPanel }     from './components/FiltersPanel.jsx';
import { ChartCard }        from './components/ChartCard.jsx';

import { TopHorizontalBar }  from './charts/TopHorizontalBar.jsx';
import { EventsByRoleChart } from './charts/EventsByRoleChart.jsx';

import { CHART_HEIGHTS, CHART_GRID_COLS } from './config.js';

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  topUsers: `Muestra los usuarios del sistema que han generado más asistencias durante el período. Refleja quiénes gestionan con mayor intensidad sus eventos.`,
  eventsByRole: `Compara la cantidad de eventos creados según el tipo de cuenta: Administrador vs. Usuario regular. Muestra cómo se distribuye la gestión de eventos entre los diferentes roles del sistema.`,
  eventsByUser: `Ranking de los usuarios que más eventos han creado en el período seleccionado. Identifica a los miembros más activos en la creación y organización de actividades.`,
};

function colClass(key) {
  return CHART_GRID_COLS[key] === 'full' ? 'col-span-1 md:col-span-2' : 'col-span-1';
}

// ---------------------------------------------------------------------------
// Ícono de calendario (inline SVG)
// ---------------------------------------------------------------------------

const CalendarIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
  </svg>
);

// ---------------------------------------------------------------------------
// Componente
// ---------------------------------------------------------------------------

export default function UsuariosApp() {
  const isDark = useTheme();
  const { state, fetchAll } = useUsuariosStats();
  const { filters, updateFilter, clearFilter } = useFilters();

  useEffect(() => {
    fetchAll(filters);
  }, [filters, fetchAll]);

  const { counters, charts, loading } = state;

  return (
    <div>
      {/* ── Filtros ── */}
      <FiltersPanel
        filters={filters}
        onUpdate={updateFilter}
        onClear={clearFilter}
        loading={loading}
      />

      {/* ── Contador de eventos ── */}
      <section className="mb-6">
        <div className="flex items-center gap-4 p-5 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm max-w-xs">
          <div className="p-3 rounded-xl bg-violet-100 dark:bg-violet-900/30">
            <span className="text-violet-600 dark:text-violet-400">
              <CalendarIcon />
            </span>
          </div>
          <div>
            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
              {loading
                ? <span className="block w-20 h-7 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
                : (counters.events ?? 0).toLocaleString('es-CO')
              }
            </p>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Eventos en el período</p>
          </div>
        </div>
      </section>

      {/* ══ Distribución por Rol ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Distribución por Rol
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className={colClass('eventsByRole')}>
            <ChartCard
              title="Eventos por Rol"
              description={DESCRIPTIONS.eventsByRole}
              height={CHART_HEIGHTS.role}
              loading={loading}
              isEmpty={!loading && charts.eventsByRole.length === 0}
              data={charts.eventsByRole}
              isDark={isDark}
              valueLabel="Eventos"
            >
              <EventsByRoleChart data={charts.eventsByRole} isDark={isDark} />
            </ChartCard>
          </div>

          <div className={colClass('eventsByUser')}>
            <ChartCard
              title="Eventos por Usuario"
              description={DESCRIPTIONS.eventsByUser}
              height={CHART_HEIGHTS.role}
              loading={loading}
              isEmpty={!loading && charts.eventsByUser.length === 0}
              data={charts.eventsByUser}
              isDark={isDark}
              valueLabel="Eventos"
            >
              <TopHorizontalBar data={charts.eventsByUser} isDark={isDark} valueLabel="eventos" />
            </ChartCard>
          </div>
        </div>
      </section>

      {/* ══ Top Usuarios ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Top Usuarios
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className={colClass('topUsers')}>
            <ChartCard
              title="Usuarios con más actividad"
              description={DESCRIPTIONS.topUsers}
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topUsers.length === 0}
              data={charts.topUsers}
              isDark={isDark}
              valueLabel="Asistencias"
            >
              <TopHorizontalBar data={charts.topUsers} isDark={isDark} valueLabel="asistencias generadas" />
            </ChartCard>
          </div>
        </div>
      </section>
    </div>
  );
}
