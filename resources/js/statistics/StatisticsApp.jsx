import React, { useState, useEffect, useCallback } from 'react';

import { useTheme }      from './hooks/useTheme.js';
import { useStatistics } from './hooks/useStatistics.js';

import { FiltersPanel }  from './components/FiltersPanel.jsx';
import { StatCounters }  from './components/StatCounters.jsx';
import { ChartCard }     from './components/ChartCard.jsx';

import { ProgramAttendancesBar }  from './charts/ProgramAttendancesBar.jsx';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { TopHorizontalBar }       from './charts/TopHorizontalBar.jsx';
import { EventsByRoleChart }      from './charts/EventsByRoleChart.jsx';

import { CHART_HEIGHTS, CHART_GRID_COLS } from './config.js';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function today() {
  return new Date().toISOString().split('T')[0];
}

function monthAgo() {
  const d = new Date();
  d.setMonth(d.getMonth() - 1);
  return d.toISOString().split('T')[0];
}

function defaultFilters() {
  return { dateFrom: monthAgo(), dateTo: today() };
}

/**
 * Devuelve las clases de columna Tailwind según la configuración del gráfico.
 * 'full' → ocupa las 2 columnas del grid en md
 * 'half' → ocupa 1 columna (media en md)
 */
function colClass(key) {
  return CHART_GRID_COLS[key] === 'full' ? 'col-span-1 md:col-span-2' : 'col-span-1';
}

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function StatisticsApp() {
  const isDark = useTheme();
  const { state, fetchAll } = useStatistics();

  // Filtros "pendientes" (lo que el usuario está editando) y
  // filtros "aplicados" (lo que actualmente está en la API).
  const [applied, setApplied]   = useState(defaultFilters);
  const [pending, setPending]   = useState(defaultFilters);

  // Cargar datos cuando cambian los filtros aplicados.
  useEffect(() => {
    fetchAll(applied);
  }, [applied, fetchAll]);

  const handleApply = useCallback(() => {
    setApplied({ ...pending });
  }, [pending]);

  const handleClear = useCallback(() => {
    const def = defaultFilters();
    setPending(def);
    setApplied(def);
  }, []);

  const { counters, charts, loading } = state;

  return (
    <div>
      {/* ── Filtros ── */}
      <FiltersPanel
        filters={pending}
        onChange={setPending}
        onApply={handleApply}
        onClear={handleClear}
        loading={loading}
      />

      {/* ── Contadores globales ── */}
      <StatCounters counters={counters} loading={loading} />

      {/* ══ Sección: Programa ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Programa
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div className={colClass('programAttendances')}>
            <ChartCard
              title="Asistencias por Programa"
              height={CHART_HEIGHTS.bar}
              loading={loading}
              isEmpty={!loading && charts.attendancesByProgram.length === 0}
              data={charts.attendancesByProgram}
              isDark={isDark}
            >
              <ProgramAttendancesBar data={charts.attendancesByProgram} isDark={isDark} />
            </ChartCard>
          </div>

          <div className={colClass('programParticipants')}>
            <ChartCard
              title="Participantes por Programa"
              height={CHART_HEIGHTS.pie}
              loading={loading}
              isEmpty={!loading && charts.participantsByProgram.length === 0}
              data={charts.participantsByProgram}
              isDark={isDark}
            >
              <ProgramParticipantsPie data={charts.participantsByProgram} isDark={isDark} />
            </ChartCard>
          </div>

        </div>
      </section>

      {/* ══ Sección: Tops ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Tops
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div className={colClass('topEvents')}>
            <ChartCard
              title="Eventos con más asistencias"
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topEvents.length === 0}
              data={charts.topEvents}
              isDark={isDark}
            >
              <TopHorizontalBar
                data={charts.topEvents}
                isDark={isDark}
                valueLabel="asistencias"
              />
            </ChartCard>
          </div>

          <div className={colClass('topParticipants')}>
            <ChartCard
              title="Participantes más frecuentes"
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topParticipants.length === 0}
              data={charts.topParticipants}
              isDark={isDark}
            >
              <TopHorizontalBar
                data={charts.topParticipants}
                isDark={isDark}
                valueLabel="asistencias"
              />
            </ChartCard>
          </div>

          <div className={colClass('topUsers')}>
            <ChartCard
              title="Usuarios con más actividad"
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topUsers.length === 0}
              data={charts.topUsers}
              isDark={isDark}
            >
              <TopHorizontalBar
                data={charts.topUsers}
                isDark={isDark}
                valueLabel="asistencias generadas"
              />
            </ChartCard>
          </div>

        </div>
      </section>

      {/* ══ Sección: Eventos por usuario ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Eventos creados por usuarios
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div className={colClass('eventsByRole')}>
            <ChartCard
              title="Por Rol"
              height={CHART_HEIGHTS.role}
              loading={loading}
              isEmpty={!loading && charts.eventsByRole.length === 0}
              data={charts.eventsByRole}
              isDark={isDark}
            >
              <EventsByRoleChart data={charts.eventsByRole} isDark={isDark} />
            </ChartCard>
          </div>

          <div className={colClass('eventsByUser')}>
            <ChartCard
              title="Por Usuario"
              height={CHART_HEIGHTS.role}
              loading={loading}
              isEmpty={!loading && charts.eventsByUser.length === 0}
              data={charts.eventsByUser}
              isDark={isDark}
            >
              <TopHorizontalBar
                data={charts.eventsByUser}
                isDark={isDark}
                valueLabel="eventos"
              />
            </ChartCard>
          </div>

        </div>
      </section>
    </div>
  );
}
