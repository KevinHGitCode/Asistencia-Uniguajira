import React, { useState, useEffect } from 'react';

import { ChartCard } from '../statistics/components/ChartCard.jsx';
// Gráfico de torta/dona unificado del módulo de estadísticas (con legend lateral, groupTopN, etc.)
import { ProgramParticipantsPie } from '../statistics/charts/ProgramParticipantsPie.jsx';

import { CHART_HEIGHTS } from '../statistics/config.js';

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

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function EventChartsApp({ eventId }) {
  const [isDark, setIsDark]           = useState(false);
  const [programData, setProgramData] = useState([]);
  const [roleData, setRoleData]       = useState([]);
  const [depData, setDepData]         = useState([]);
  const [orgData, setOrgData]         = useState([]);
  const [sexData, setSexData]         = useState([]);
  const [groupData, setGroupData]     = useState([]);
  const [loading, setLoading]         = useState(true);
  const [error, setError]             = useState(null);

  // Detectar modo oscuro
  useEffect(() => {
    setIsDark(document.documentElement.classList.contains('dark'));
    const observer = new MutationObserver(() =>
      setIsDark(document.documentElement.classList.contains('dark'))
    );
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    return () => observer.disconnect();
  }, []);

  // Cargar datos del evento
  useEffect(() => {
    if (!eventId) return;
    setLoading(true);
    setError(null);

    const base = `/api/statistics/event/${eventId}`;
    Promise.all([
      fetch(`${base}/programs`).then(r => r.json()),
      fetch(`${base}/roles`).then(r => r.json()),
      fetch(`${base}/dependencies`).then(r => r.json()),
      fetch(`${base}/organizations`).then(r => r.json()),
      fetch(`${base}/sex`).then(r => r.json()),
      fetch(`${base}/group`).then(r => r.json()),
    ])
      .then(([programs, roles, deps, orgs, sex, group]) => {
        setProgramData(programs.map(d => ({ name: d.program, value: d.count })));
        setRoleData(roles.map(d => ({ name: d.role,    value: d.count })));
        setDepData(deps.map(d => ({ name: d.label,     value: d.count })));
        setOrgData(orgs.map(d => ({ name: d.label,     value: d.count })));
        setSexData(sex.map(d => ({ name: d.label,      value: d.count })));
        setGroupData(group.map(d => ({ name: d.label,   value: d.count })));
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, [eventId]);

  if (error) {
    return (
      <div className="flex items-center justify-center py-12 text-center text-red-500">
        <p>Error al cargar los gráficos: {error}</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-8">

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
