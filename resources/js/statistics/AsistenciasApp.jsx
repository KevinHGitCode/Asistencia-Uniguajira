import React, { useCallback, useEffect } from 'react';

import { useTheme }            from './hooks/useTheme.js';
import { useAsistenciasStats } from './hooks/useAsistenciasStats.js';
import { useFilters }          from './hooks/useFilters.js';
import { useEventFilter }      from './hooks/useEventFilter.js';
import { useCampusRefresh }    from './hooks/useCampusRefresh.js';

import { FiltersPanel }          from './components/FiltersPanel.jsx';
import { StatCounters }          from './components/StatCounters.jsx';
import { ChartCard }             from './components/ChartCard.jsx';
import { EventFilterButton, EventFilterPanel } from './components/EventFilterPanel.jsx';

import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { TopHorizontalBar }      from './charts/TopHorizontalBar.jsx';

import { CHART_HEIGHTS, CHART_GRID_COLS } from './config.js';

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programAttendances: `Compara el número total de asistencias registradas en cada programa académico durante el período seleccionado. Solo incluye asistencias con rol vinculado a un programa (Estudiante, Docente, Graduado); no incluye administrativos ni comunidad externa.`,
  dependencyAttendances: `Distribución de asistencias registradas por dependencia universitaria (Administrativos). Un mismo participante puede sumar varias asistencias si asistió a múltiples eventos.`,
  organizationAttendances: `Distribución de asistencias registradas por organización o institución externa (Comunidad Externa). Un mismo participante puede sumar varias asistencias si asistió a múltiples eventos.`,
  topEvents: `Ranking de los 5 eventos que han reunido más asistentes en el período. Ayuda a identificar qué actividades generan mayor convocatoria y tienen más impacto.`,
  topParticipants: `Lista los 5 participantes que han asistido a más eventos durante el período, ordenados de mayor a menor. Útil para reconocer a los asistentes más comprometidos o frecuentes.`,
  byRole:  `Distribución de las asistencias registradas según el estamento del participante (Estudiante / Docente). Un mismo participante puede sumar varias asistencias si asistió a múltiples eventos.`,
  bySex:   `Distribución de las asistencias registradas según el sexo del participante (Masculino, Femenino, Otro). Refleja el volumen de asistencia, no personas únicas.`,
  byGroup: `Distribución de las asistencias registradas según el grupo poblacional priorizado del participante (Indígena, Afrodescendiente, Raizal, Palenquero, Rom, Ninguno, etc.).`,
};

function colClass(key) {
  return CHART_GRID_COLS[key] === 'full' ? 'col-span-1 md:col-span-2' : 'col-span-1';
}

// ---------------------------------------------------------------------------
// Componente
// ---------------------------------------------------------------------------

export default function AsistenciasApp() {
  const isDark = useTheme();
  const { state, fetchAll }            = useAsistenciasStats();
  const { filters, updateFilter, clearFilter } = useFilters();
  const evFilter = useEventFilter(filters);

  useEffect(() => {
    fetchAll(filters, evFilter.effectiveEventIds);
  }, [filters, evFilter.effectiveEventIds, fetchAll]);

  useCampusRefresh(useCallback(() => {
    fetchAll(filters, evFilter.effectiveEventIds);
  }, [filters, evFilter.effectiveEventIds, fetchAll]));

  const { counters, charts, loading } = state;

  return (
    <div>
      {/* ── Filtros ── */}
      <FiltersPanel
        filters={filters}
        onUpdate={updateFilter}
        onClear={clearFilter}
        loading={loading}
        actions={
          <EventFilterButton
            open={evFilter.open}
            onToggle={() => evFilter.setOpen(o => !o)}
            isFiltered={evFilter.isFiltered}
            filteredCount={evFilter.filteredCount}
            totalCount={evFilter.totalCount}
            loading={evFilter.evLoading}
          />
        }
      />

      {/* ── Selector de eventos (expandible) ── */}
      {evFilter.open && (
        <EventFilterPanel
          events={evFilter.events}
          loading={evFilter.evLoading}
          checkedIds={evFilter.checkedIds}
          onToggle={evFilter.toggle}
          onSelectAll={evFilter.selectAll}
          onClearAll={evFilter.clearAll}
          isFiltered={evFilter.isFiltered}
          filteredCount={evFilter.filteredCount}
          totalCount={evFilter.totalCount}
        />
      )}

      {/* ── Contadores ── */}
      <StatCounters counters={counters} loading={loading} />

      {/* ══ Asistencias por Estamento — GRANDE ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Asistencias por Estamento
        </h2>
        <ChartCard
          title="Asistencias por Estamento"
          description={DESCRIPTIONS.byRole}
          height={CHART_HEIGHTS.bar}
          loading={loading}
          isEmpty={!loading && charts.byRole.length === 0}
          data={charts.byRole}
          isDark={isDark}
          valueLabel="Asistencias"
        >
          <ProgramParticipantsPie data={charts.byRole} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistencias" />
        </ChartCard>
      </section>

      {/* ══ Asistencias por Programa / Dependencia / Organización — reducidas ══ */}
      <section className="mb-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">

          <ChartCard
            title="Asistencias por Programa Académico"
            description={DESCRIPTIONS.programAttendances}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && charts.attendancesByProgram.length === 0}
            data={charts.attendancesByProgram}
            isDark={isDark}
            valueLabel="Asistencias"
          >
            <ProgramParticipantsPie data={charts.attendancesByProgram} isDark={isDark} showOuterLabels={false} valueLabel="Asistencias" />
          </ChartCard>

          <ChartCard
            title="Asistencias por Dependencia"
            description={DESCRIPTIONS.dependencyAttendances}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && charts.attendancesByDependency.length === 0}
            data={charts.attendancesByDependency}
            isDark={isDark}
            valueLabel="Asistencias"
          >
            <ProgramParticipantsPie data={charts.attendancesByDependency} isDark={isDark} showOuterLabels={false} valueLabel="Asistencias" />
          </ChartCard>

          <ChartCard
            title="Asistencias por Organización"
            description={DESCRIPTIONS.organizationAttendances}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && charts.attendancesByOrganization.length === 0}
            data={charts.attendancesByOrganization}
            isDark={isDark}
            valueLabel="Asistencias"
          >
            <ProgramParticipantsPie data={charts.attendancesByOrganization} isDark={isDark} showOuterLabels={false} valueLabel="Asistencias" />
          </ChartCard>

        </div>
      </section>

      {/* ══ Sin clasificar ══ */}
      {!loading && charts.attendancesUnclassified > 0 && (
        <div className="mb-6 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-800 dark:text-amber-300 flex items-center justify-between gap-3 flex-wrap">
          <span>
            <strong>{charts.attendancesUnclassified.toLocaleString('es-CO')}</strong> asistencia(s) no están vinculadas a ningún programa, dependencia ni organización (sin clasificar).
          </span>
          <a
            href="/administracion/participants?filtro=sin_clasificar"
            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium transition-colors shrink-0 no-underline"
          >
            Ver participantes
          </a>
        </div>
      )}

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
              valueLabel="Asistencias"
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
              valueLabel="Asistencias"
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

          {/* Por Sexo */}
          <ChartCard
            title="Por Sexo"
            description={DESCRIPTIONS.bySex}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && charts.bySex.length === 0}
            data={charts.bySex}
            isDark={isDark}
            valueLabel="Asistencias"
          >
            <ProgramParticipantsPie data={charts.bySex} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistencias" />
          </ChartCard>

          {/* Por Grupo Priorizado */}
          <ChartCard
            title="Por Grupo Priorizado"
            description={DESCRIPTIONS.byGroup}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && charts.byGroup.length === 0}
            data={charts.byGroup}
            isDark={isDark}
            valueLabel="Asistencias"
          >
            <ProgramParticipantsPie data={charts.byGroup} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistencias" />
          </ChartCard>

        </div>
      </section>
    </div>
  );
}
