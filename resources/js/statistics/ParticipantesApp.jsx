import React, { useEffect } from 'react';

import { useTheme }              from './hooks/useTheme.js';
import { useParticipantesStats } from './hooks/useParticipantesStats.js';
import { useFilters }            from './hooks/useFilters.js';

import { FiltersPanel }          from './components/FiltersPanel.jsx';
import { ChartCard }             from './components/ChartCard.jsx';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';

import { CHART_HEIGHTS, CHART_DENSITY } from './config.js';

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programParticipants: `Muestra cómo se distribuyen los participantes únicos entre los diferentes programas. Todos los programas que representen ${CHART_DENSITY.pieMinPercent}% o menos del total se agrupan automáticamente en la categoría "Otros".`,
};

// ---------------------------------------------------------------------------
// Componente contador reutilizable (participantes únicos)
// ---------------------------------------------------------------------------

const PersonIcon = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
  </svg>
);

function ParticipantCounter({ value, loading }) {
  return (
    <div className="flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm max-w-xs">
      <div className="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 shrink-0">
        <span className="text-emerald-600 dark:text-emerald-400">
          <PersonIcon />
        </span>
      </div>
      <div>
        <p className="text-lg font-bold text-gray-900 dark:text-gray-100 leading-tight">
          {loading
            ? <span className="block w-14 h-5 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
            : (value ?? 0).toLocaleString('es-CO')
          }
        </p>
        <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Participantes únicos</p>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// App principal del módulo
// ---------------------------------------------------------------------------

export default function ParticipantesApp() {
  const isDark = useTheme();
  const { state, fetchAll } = useParticipantesStats();
  const { filters, updateFilter, clearFilter } = useFilters();

  useEffect(() => {
    fetchAll(filters);
  }, [filters, fetchAll]);

  const { counters, charts, loading } = state;

  return (
    <div>
      {/* ── Filtros (mismo componente que los otros módulos) ── */}
      <FiltersPanel
        filters={filters}
        onUpdate={updateFilter}
        onClear={clearFilter}
        loading={loading}
      />

      {/* ── Contador de participantes únicos ── */}
      <section className="mb-5">
        <ParticipantCounter value={counters.participants} loading={loading} />
      </section>

      {/* ══ Distribución por Programa ══ */}
      <section className="mb-8">
        <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
          Distribución por Programa
        </h2>
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
      </section>
    </div>
  );
}
