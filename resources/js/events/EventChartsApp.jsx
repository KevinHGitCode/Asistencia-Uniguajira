import React, { useState, useEffect } from 'react';

import { ChartCard } from '../statistics/components/ChartCard.jsx';
// Gráficos propios del módulo de eventos
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { ProgramParticipantsBar } from './charts/ProgramParticipantsBar.jsx';
import { RoleParticipantsPie }    from './charts/RoleParticipantsPie.jsx';
import { RoleParticipantsBar }    from './charts/RoleParticipantsBar.jsx';
// Gráfico de torta genérico con soporte de showOuterLabels/groupOthers
import { ProgramParticipantsPie as DemoPie } from '../statistics/charts/ProgramParticipantsPie.jsx';

// ---------------------------------------------------------------------------
// Descripciones
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programPie:  `Distribución porcentual de los asistentes al evento según el programa académico al que pertenecen. Permite identificar qué programas tuvieron mayor representación.`,
  programBar:  `Número de asistentes por programa académico en este evento. La barra más alta indica el programa con mayor participación.`,
  rolePie:     `Distribución de los asistentes según su estamento: Estudiante o Docente. Muestra la proporción de cada grupo en el evento.`,
  roleBar:     `Cantidad de asistentes por estamento (Estudiante / Docente). Útil para entender qué tipo de comunidad universitaria participó más en el evento.`,
  demoRole:    `Distribución de los asistentes al evento por estamento (Estudiante / Docente).`,
  demoSex:     `Distribución de los asistentes al evento por sexo (Masculino, Femenino, Otro).`,
  demoGroup:   `Distribución de los asistentes al evento según su grupo poblacional priorizado (Indígena, Afrodescendiente, Raizal, Palenquero, Rom, Ninguno, etc.).`,
};

const HEIGHT      = 320;
const HEIGHT_DEMO = 260;

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
      fetch(`${base}/sex`).then(r => r.json()),
      fetch(`${base}/group`).then(r => r.json()),
    ])
      .then(([programs, roles, sex, group]) => {
        setProgramData(programs.map(d => ({ name: d.program, value: d.count })));
        setRoleData(roles.map(d => ({ name: d.role,    value: d.count })));
        setSexData(sex.map(d => ({ name: d.label,    value: d.count })));
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

  const programEmpty = !loading && programData.length === 0;
  const roleEmpty    = !loading && roleData.length === 0;
  const demoEmpty    = !loading && roleData.length === 0 && sexData.length === 0 && groupData.length === 0;

  return (
    <div className="flex flex-col gap-8">

      {/* ══ Distribución por Programa ══ */}
      <section>
        <SectionTitle>Distribución por Programa</SectionTitle>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <ChartCard
            title="Distribución por Programa"
            description={DESCRIPTIONS.programPie}
            height={HEIGHT}
            loading={loading}
            isEmpty={programEmpty}
            data={programData}
            isDark={isDark}
          >
            <ProgramParticipantsPie data={programData} isDark={isDark} />
          </ChartCard>

          <ChartCard
            title="Participación por Programa"
            description={DESCRIPTIONS.programBar}
            height={HEIGHT}
            loading={loading}
            isEmpty={programEmpty}
            data={programData}
            isDark={isDark}
          >
            <ProgramParticipantsBar data={programData} isDark={isDark} />
          </ChartCard>

        </div>
      </section>

      {/* ══ Participación por Estamento ══ */}
      <section>
        <SectionTitle>Participación por Estamento</SectionTitle>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

          <ChartCard
            title="Distribución por Estamento"
            description={DESCRIPTIONS.rolePie}
            height={HEIGHT}
            loading={loading}
            isEmpty={roleEmpty}
            data={roleData}
            isDark={isDark}
          >
            <RoleParticipantsPie data={roleData} isDark={isDark} />
          </ChartCard>

          <ChartCard
            title="Participación por Estamento"
            description={DESCRIPTIONS.roleBar}
            height={HEIGHT}
            loading={loading}
            isEmpty={roleEmpty}
            data={roleData}
            isDark={isDark}
          >
            <RoleParticipantsBar data={roleData} isDark={isDark} />
          </ChartCard>

        </div>
      </section>

      {/* ══ Perfil Demográfico ══ */}
      <section>
        <SectionTitle>Perfil Demográfico</SectionTitle>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">

          <ChartCard
            title="Por Estamento"
            description={DESCRIPTIONS.demoRole}
            height={HEIGHT_DEMO}
            loading={loading}
            isEmpty={!loading && roleData.length === 0}
            data={roleData}
            isDark={isDark}
          >
            <DemoPie data={roleData} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

          <ChartCard
            title="Por Sexo"
            description={DESCRIPTIONS.demoSex}
            height={HEIGHT_DEMO}
            loading={loading}
            isEmpty={!loading && sexData.length === 0}
            data={sexData}
            isDark={isDark}
          >
            <DemoPie data={sexData} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

          <ChartCard
            title="Por Grupo Priorizado"
            description={DESCRIPTIONS.demoGroup}
            height={HEIGHT_DEMO}
            loading={loading}
            isEmpty={!loading && groupData.length === 0}
            data={groupData}
            isDark={isDark}
          >
            <DemoPie data={groupData} isDark={isDark} showOuterLabels={false} groupOthers={false} />
          </ChartCard>

        </div>
      </section>

    </div>
  );
}
