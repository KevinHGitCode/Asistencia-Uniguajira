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

import { CHART_HEIGHTS, CHART_GRID_COLS, CHART_DENSITY } from './config.js';

// ---------------------------------------------------------------------------
// Descripciones de cada gráfico (se muestran en el modal "Sobre este gráfico")
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programAttendances: `Compara el número total de asistencias registradas en cada programa académico durante el período seleccionado. Permite identificar qué programas concentran la mayor actividad.`,

  programParticipants: `Muestra cómo se distribuyen los participantes únicos entre los diferentes programas. Los segmentos con menos del ${CHART_DENSITY.pieMinPercent}% del total se agrupan automáticamente en la categoría "Otros".`,

  topEvents: `Ranking de los eventos que han reunido más asistentes en el período. Ayuda a identificar qué actividades generan mayor convocatoria y tienen más impacto.`,

  topParticipants: `Lista los participantes que han asistido a más eventos durante el período, ordenados de mayor a menor. Útil para reconocer a los asistentes más comprometidos o frecuentes.`,

  topUsers: `Muestra los usuarios del sistema que han registrado más asistencias durante el período. Refleja quiénes gestionan con mayor intensidad sus eventos.`,

  eventsByRole: `Compara la cantidad de eventos creados según el tipo de cuenta: Administrador vs. Usuario regular. Muestra cómo se distribuye la gestión de eventos entre los diferentes roles del sistema.`,

  eventsByUser: `Ranking de los usuarios que más eventos han creado en el período seleccionado. Identifica a los miembros más activos en la creación y organización de actividades.`,
};

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

function colClass(key) {
  return CHART_GRID_COLS[key] === 'full' ? 'col-span-1 md:col-span-2' : 'col-span-1';
}

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function StatisticsApp() {
  const isDark = useTheme();
  const { state, fetchAll } = useStatistics();

  const [applied, setApplied] = useState(defaultFilters);
  const [pending, setPending] = useState(defaultFilters);

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
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Programa</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div className={colClass('programAttendances')}>
            <ChartCard
              title="Asistencias por Programa"
              description={DESCRIPTIONS.programAttendances}
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
              description={DESCRIPTIONS.programParticipants}
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
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Tops</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div className={colClass('topEvents')}>
            <ChartCard
              title="Eventos con más asistencias"
              description={DESCRIPTIONS.topEvents}
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topEvents.length === 0}
              data={charts.topEvents}
              isDark={isDark}
            >
              <TopHorizontalBar data={charts.topEvents} isDark={isDark} valueLabel="asistencias" />
            </ChartCard>
          </div>

          <div className={colClass('topParticipants')}>
            <ChartCard
              title="Participantes más frecuentes"
              description={DESCRIPTIONS.topParticipants}
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topParticipants.length === 0}
              data={charts.topParticipants}
              isDark={isDark}
            >
              <TopHorizontalBar data={charts.topParticipants} isDark={isDark} valueLabel="asistencias" />
            </ChartCard>
          </div>

          <div className={colClass('topUsers')}>
            <ChartCard
              title="Usuarios con más actividad"
              description={DESCRIPTIONS.topUsers}
              height={CHART_HEIGHTS.horizontalBar}
              loading={loading}
              isEmpty={!loading && charts.topUsers.length === 0}
              data={charts.topUsers}
              isDark={isDark}
            >
              <TopHorizontalBar data={charts.topUsers} isDark={isDark} valueLabel="asistencias generadas" />
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
              description={DESCRIPTIONS.eventsByRole}
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
              description={DESCRIPTIONS.eventsByUser}
              height={CHART_HEIGHTS.role}
              loading={loading}
              isEmpty={!loading && charts.eventsByUser.length === 0}
              data={charts.eventsByUser}
              isDark={isDark}
            >
              <TopHorizontalBar data={charts.eventsByUser} isDark={isDark} valueLabel="eventos" />
            </ChartCard>
          </div>

        </div>
      </section>
    </div>
  );
}
