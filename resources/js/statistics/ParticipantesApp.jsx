import React, { useEffect } from 'react';

import { useTheme }              from './hooks/useTheme.js';
import { useParticipantesStats } from './hooks/useParticipantesStats.js';

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
// Ícono de persona (inline SVG — evita importar Heroicons solo para esto)
// ---------------------------------------------------------------------------

const PersonIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
  </svg>
);

const InfoIcon = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} className="w-4 h-4 shrink-0">
    <circle cx="12" cy="12" r="10" />
    <line x1="12" y1="16" x2="12" y2="12" />
    <line x1="12" y1="8" x2="12.01" y2="8" />
  </svg>
);

// ---------------------------------------------------------------------------
// Componente
// ---------------------------------------------------------------------------

export default function ParticipantesApp() {
  const isDark = useTheme();
  const { state, fetchAll } = useParticipantesStats();

  useEffect(() => {
    fetchAll();
  }, [fetchAll]);

  const { counters, charts, loading } = state;

  return (
    <div>
      {/* ── Nota: sin filtros ── */}
      <div className="mb-6 flex items-center gap-2 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-sm text-blue-700 dark:text-blue-300">
        <InfoIcon />
        <span>
          Los datos de participantes únicos son{' '}
          <strong>totales históricos</strong> y no aplican filtros de fecha.
        </span>
      </div>

      {/* ── Contador de participantes ── */}
      <section className="mb-6">
        <div className="flex items-center gap-4 p-5 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm max-w-xs">
          <div className="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
            <span className="text-emerald-600 dark:text-emerald-400">
              <PersonIcon />
            </span>
          </div>
          <div>
            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
              {loading
                ? <span className="block w-20 h-7 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
                : (counters.participants ?? 0).toLocaleString('es-CO')
              }
            </p>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Participantes únicos</p>
          </div>
        </div>
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
