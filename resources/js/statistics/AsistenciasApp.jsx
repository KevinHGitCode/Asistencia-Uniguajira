import React, { useEffect } from 'react';

import { useTheme }            from './hooks/useTheme.js';
import { useAsistenciasStats } from './hooks/useAsistenciasStats.js';
import { useDemografiaStats }  from './hooks/useDemografiaStats.js';
import { useFilters }          from './hooks/useFilters.js';

import { FiltersPanel }          from './components/FiltersPanel.jsx';
import { StatCounters }          from './components/StatCounters.jsx';
import { ChartCard }             from './components/ChartCard.jsx';

import { ProgramAttendancesBar } from './charts/ProgramAttendancesBar.jsx';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { TopHorizontalBar }      from './charts/TopHorizontalBar.jsx';

import { CHART_HEIGHTS, CHART_GRID_COLS } from './config.js';

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programAttendances: `Compara el número total de asistencias registradas en cada programa académico durante el período seleccionado. Permite identificar qué programas concentran la mayor actividad.`,
  topEvents: `Ranking de los eventos que han reunido más asistentes en el período. Ayuda a identificar qué actividades generan mayor convocatoria y tienen más impacto.`,
  topParticipants: `Lista los participantes que han asistido a más eventos durante el período, ordenados de mayor a menor. Útil para reconocer a los asistentes más comprometidos o frecuentes.`,
  byRole: `Distribución de los participantes únicos según su estamento: Estudiante o Docente. Solo se cuentan participantes con al menos una asistencia en el período.`,
  bySex: `Distribución de los participantes únicos por sexo (Masculino, Femenino, Otro). Solo se cuentan participantes con al menos una asistencia en el período seleccionado.`,
  byGroup: `Distribución de los participantes únicos según su grupo poblacional priorizado (Indígena, Afrodescendiente, Raizal, Palenquero, Rom, Ninguno, etc.). Solo se cuentan asistentes del período.`,
};

function colClass(key) {
  return CHART_GRID_COLS[key] === 'full' ? 'col-span-1 md:col-span-2' : 'col-span-1';
}

// ---------------------------------------------------------------------------
// Componente
// ---------------------------------------------------------------------------

export default function AsistenciasApp() {
  const isDark = useTheme();
  const { state, fetchAll }                   = useAsistenciasStats();
  const { state: demoState, fetchAll: fetchDemografia } = useDemografiaStats();
  const { filters, updateFilter, clearFilter } = useFilters();

  useEffect(() => {
    fetchAll(filters);
  }, [filters, fetchAll]);

  useEffect(() => {
    fetchDemografia(filters);
  }, [filters, fetchDemografia]);

  const { counters, charts, loading }       = state;
  const { charts: demo, loading: demoLoad } = demoState;

  return (
    <div>
      {/* ── Filtros ── */}
      <FiltersPanel
        filters={filters}
        onUpdate={updateFilter}
        onClear={clearFilter}
        loading={loading || demoLoad}
      />

      {/* ── Contadores ── */}
      <StatCounters counters={counters} loading={loading} />

      {/* ══ Actividad por Programa ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Actividad por Programa
        </h2>
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
        </div>
      </section>

      {/* ══ Tops ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Tops
        </h2>
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
        </div>
      </section>

      {/* ══ Perfil Demográfico ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Perfil Demográfico
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          {/* Por Estamento */}
          <ChartCard
            title="Por Estamento"
            description={DESCRIPTIONS.byRole}
            height={CHART_HEIGHTS.role}
            loading={demoLoad}
            isEmpty={!demoLoad && demo.byRole.length === 0}
            data={demo.byRole}
            isDark={isDark}
          >
            <ProgramParticipantsPie data={demo.byRole} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

          {/* Por Sexo */}
          <ChartCard
            title="Por Sexo"
            description={DESCRIPTIONS.bySex}
            height={CHART_HEIGHTS.role}
            loading={demoLoad}
            isEmpty={!demoLoad && demo.bySex.length === 0}
            data={demo.bySex}
            isDark={isDark}
          >
            <ProgramParticipantsPie data={demo.bySex} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

          {/* Por Grupo Priorizado */}
          <ChartCard
            title="Por Grupo Priorizado"
            description={DESCRIPTIONS.byGroup}
            height={CHART_HEIGHTS.role}
            loading={demoLoad}
            isEmpty={!demoLoad && demo.byGroup.length === 0}
            data={demo.byGroup}
            isDark={isDark}
          >
            <ProgramParticipantsPie data={demo.byGroup} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

        </div>
      </section>
    </div>
  );
}
