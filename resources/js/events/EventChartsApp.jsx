import React, { useState, useEffect, useCallback } from 'react';

import { ChartCard } from '../statistics/components/ChartCard.jsx';
// Gráfico de torta/dona unificado del módulo de estadísticas (con legend lateral, groupTopN, etc.)
import { ProgramParticipantsPie } from '../statistics/charts/ProgramParticipantsPie.jsx';

import { CHART_HEIGHTS } from '../statistics/config.js';

// Cada cuánto se refrescan las estadísticas mientras el evento sigue abierto.
const POLL_MS = 20000;

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  byRole:         `Distribución de los asistentes al evento según su estamento (Estudiante, Docente, etc.). Muestra la proporción de cada grupo en el evento.`,
  byProgram:      `Distribución porcentual de los asistentes al evento según el programa académico al que pertenecen. Permite identificar qué programas tuvieron mayor representación.`,
  byDependency:   `Distribución de los asistentes al evento por dependencia universitaria (Administrativos). Un mismo participante puede aparecer una vez por asistencia registrada.`,
  byOrganization: `Distribución de los asistentes al evento por organización o institución externa (Comunidad Externa).`,
  demoSex:        `Distribución de los asistentes al evento por sexo (Masculino, Femenino, Otro).`,
  demoGroup:      `Distribución de los asistentes al evento según su grupo poblacional priorizado (Indígena, Afrodescendiente, Raizal, Palenquero, Rom, Ninguno, etc.).`,
};

// ---------------------------------------------------------------------------
// Heading de sección reutilizable
// ---------------------------------------------------------------------------

function SectionTitle({ children }) {
  return (
    <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
      {children}
    </h2>
  );
}

// Indicador "en vivo": el evento está abierto y las estadísticas se refrescan solas.
function LiveBadge({ updatedAt }) {
  return (
    <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
      <span className="relative flex h-2.5 w-2.5">
        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
        <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500" />
      </span>
      <span className="font-medium text-emerald-600 dark:text-emerald-400">En vivo</span>
      <span>· se actualiza automáticamente{updatedAt ? ` · ${updatedAt}` : ''}</span>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function EventChartsApp({ eventId, open = false }) {
  const [isDark, setIsDark]           = useState(false);
  const [programData, setProgramData] = useState([]);
  const [roleData, setRoleData]       = useState([]);
  const [depData, setDepData]         = useState([]);
  const [orgData, setOrgData]         = useState([]);
  const [sexData, setSexData]         = useState([]);
  const [groupData, setGroupData]     = useState([]);
  const [loading, setLoading]         = useState(true);
  const [error, setError]             = useState(null);
  const [updatedAt, setUpdatedAt]     = useState(null);

  // Detectar modo oscuro
  useEffect(() => {
    setIsDark(document.documentElement.classList.contains('dark'));
    const observer = new MutationObserver(() =>
      setIsDark(document.documentElement.classList.contains('dark'))
    );
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    return () => observer.disconnect();
  }, []);

  // Carga de datos. `silent` = refresco de polling (no muestra el spinner ni
  // pisa los datos actuales si falla).
  const fetchData = useCallback((signal, { silent = false } = {}) => {
    if (!eventId) return Promise.resolve();
    if (!silent) { setLoading(true); setError(null); }

    const base = `/api/statistics/event/${eventId}`;
    return Promise.all([
      fetch(`${base}/programs`, { signal }).then(r => r.json()),
      fetch(`${base}/roles`, { signal }).then(r => r.json()),
      fetch(`${base}/dependencies`, { signal }).then(r => r.json()),
      fetch(`${base}/organizations`, { signal }).then(r => r.json()),
      fetch(`${base}/sex`, { signal }).then(r => r.json()),
      fetch(`${base}/group`, { signal }).then(r => r.json()),
    ])
      .then(([programs, roles, deps, orgs, sex, group]) => {
        setProgramData(programs.map(d => ({ name: d.program, value: d.count })));
        setRoleData(roles.map(d => ({ name: d.role,    value: d.count })));
        setDepData(deps.map(d => ({ name: d.label,     value: d.count })));
        setOrgData(orgs.map(d => ({ name: d.label,     value: d.count })));
        setSexData(sex.map(d => ({ name: d.label,      value: d.count })));
        setGroupData(group.map(d => ({ name: d.label,   value: d.count })));
        setError(null);
        setUpdatedAt(new Date().toLocaleTimeString('es-CO'));
      })
      .catch(err => {
        if (err.name === 'AbortError') return;
        if (!silent) setError(err.message);
      })
      .finally(() => { if (!silent) setLoading(false); });
  }, [eventId]);

  // Carga inicial
  useEffect(() => {
    const controller = new AbortController();
    fetchData(controller.signal);
    return () => controller.abort();
  }, [fetchData]);

  // Refresco automático mientras el evento sigue abierto (polling).
  useEffect(() => {
    if (!open || !eventId) return undefined;

    let controller = null;
    const id = setInterval(() => {
      if (document.hidden) return; // no refrescar en pestaña oculta
      controller = new AbortController();
      fetchData(controller.signal, { silent: true });
    }, POLL_MS);

    return () => { clearInterval(id); if (controller) controller.abort(); };
  }, [open, eventId, fetchData]);

  if (error) {
    return (
      <div className="flex items-center justify-center py-12 text-center text-red-500">
        <p>Error al cargar los gráficos: {error}</p>
      </div>
    );
  }

  const hasAnyData = programData.length || roleData.length || depData.length
    || orgData.length || sexData.length || groupData.length;

  // Evento en curso sin asistentes aún: estado de espera que se llenará solo.
  if (!loading && !hasAnyData) {
    return (
      <div className="flex flex-col items-center justify-center gap-3 py-12 text-center text-gray-500 dark:text-gray-400">
        {open && <LiveBadge updatedAt={updatedAt} />}
        <svg className="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <h3 className="text-lg font-semibold">Esperando asistentes</h3>
          <p className="text-sm">
            {open
              ? 'Las estadísticas aparecerán automáticamente al registrarse asistencias.'
              : 'No hay asistentes registrados.'}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-8">

      {open && <LiveBadge updatedAt={updatedAt} />}

      {/* ══ Asistentes por Estamento — ancho completo ══ */}
      <section>
        <SectionTitle>Asistentes por Estamento</SectionTitle>
        <ChartCard
          title="Asistentes por Estamento"
          description={DESCRIPTIONS.byRole}
          height={CHART_HEIGHTS.bar}
          loading={loading}
          isEmpty={!loading && roleData.length === 0}
          data={roleData}
          isDark={isDark}
          valueLabel="Asistentes"
        >
          <ProgramParticipantsPie data={roleData} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistentes" />
        </ChartCard>
      </section>

      {/* ══ Programa / Dependencia / Organización — 3 columnas ══ */}
      <section>
        <SectionTitle>Distribución por Clasificación</SectionTitle>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">

          <ChartCard
            title="Por Programa Académico"
            description={DESCRIPTIONS.byProgram}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && programData.length === 0}
            data={programData}
            isDark={isDark}
            valueLabel="Asistentes"
          >
            <ProgramParticipantsPie data={programData} isDark={isDark} showOuterLabels={false} valueLabel="Asistentes" />
          </ChartCard>

          <ChartCard
            title="Por Dependencia"
            description={DESCRIPTIONS.byDependency}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && depData.length === 0}
            data={depData}
            isDark={isDark}
            valueLabel="Asistentes"
          >
            <ProgramParticipantsPie data={depData} isDark={isDark} showOuterLabels={false} valueLabel="Asistentes" />
          </ChartCard>

          <ChartCard
            title="Por Organización"
            description={DESCRIPTIONS.byOrganization}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && orgData.length === 0}
            data={orgData}
            isDark={isDark}
            valueLabel="Asistentes"
          >
            <ProgramParticipantsPie data={orgData} isDark={isDark} showOuterLabels={false} valueLabel="Asistentes" />
          </ChartCard>

        </div>
      </section>

      {/* ══ Perfil Demográfico — 2 columnas ══ */}
      <section>
        <SectionTitle>Perfil Demográfico</SectionTitle>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <ChartCard
            title="Por Sexo"
            description={DESCRIPTIONS.demoSex}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && sexData.length === 0}
            data={sexData}
            isDark={isDark}
            valueLabel="Asistentes"
          >
            <ProgramParticipantsPie data={sexData} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistentes" />
          </ChartCard>

          <ChartCard
            title="Por Grupo Priorizado"
            description={DESCRIPTIONS.demoGroup}
            height={CHART_HEIGHTS.role}
            loading={loading}
            isEmpty={!loading && groupData.length === 0}
            data={groupData}
            isDark={isDark}
            valueLabel="Asistentes"
          >
            <ProgramParticipantsPie data={groupData} isDark={isDark} showOuterLabels={false} groupOthers={false} valueLabel="Asistentes" />
          </ChartCard>

        </div>
      </section>

    </div>
  );
}
