import React, { useState, useEffect } from 'react';

import { ChartCard } from '../statistics/components/ChartCard.jsx';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { ProgramParticipantsBar } from './charts/ProgramParticipantsBar.jsx';
import { RoleParticipantsPie }    from './charts/RoleParticipantsPie.jsx';
import { RoleParticipantsBar }    from './charts/RoleParticipantsBar.jsx';

// ---------------------------------------------------------------------------
// Descripciones de cada gráfico
// ---------------------------------------------------------------------------

const DESCRIPTIONS = {
  programPie: `Distribución porcentual de los asistentes al evento según el programa académico al que pertenecen. Permite identificar qué programas tuvieron mayor representación.`,
  programBar: `Número de asistentes por programa académico en este evento. La barra más alta indica el programa con mayor participación.`,
  rolePie: `Distribución de los asistentes según su estamento: Estudiante o Docente. Muestra la proporción de cada grupo en el evento.`,
  roleBar: `Cantidad de asistentes por estamento (Estudiante / Docente). Útil para entender qué tipo de comunidad universitaria participó más en el evento.`,
};

// Altura de los gráficos (en px)
const HEIGHT = 320;

// ---------------------------------------------------------------------------
// Componente principal
// ---------------------------------------------------------------------------

export default function EventChartsApp({ eventId }) {
  const [isDark, setIsDark]           = useState(false);
  const [programData, setProgramData] = useState([]);
  const [roleData, setRoleData]       = useState([]);
  const [loading, setLoading]         = useState(true);
  const [error, setError]             = useState(null);

  // Detect dark mode
  useEffect(() => {
    const dark = document.documentElement.classList.contains('dark');
    setIsDark(dark);

    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.classList.contains('dark'));
    });

    observer.observe(document.documentElement, {
      attributes:      true,
      attributeFilter: ['class'],
    });

    return () => observer.disconnect();
  }, []);

  // Fetch data
  useEffect(() => {
    if (!eventId) return;

    setLoading(true);
    setError(null);

    Promise.all([
      fetch(`/api/statistics/event/${eventId}/programs`).then(r => r.json()),
      fetch(`/api/statistics/event/${eventId}/roles`).then(r => r.json()),
    ])
      .then(([programs, roles]) => {
        setProgramData(programs.map(item => ({ name: item.program, value: item.count })));
        setRoleData(roles.map(item => ({ name: item.role,    value: item.count })));
      })
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, [eventId]);

  if (error) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center text-red-500">
          <p>Error al cargar los gráficos: {error}</p>
        </div>
      </div>
    );
  }

  const programEmpty = !loading && programData.length === 0;
  const roleEmpty    = !loading && roleData.length === 0;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

      {/* Distribución por Programa (Pie) */}
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

      {/* Participación por Programa (Bar) */}
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

      {/* Distribución por Estamento (Pie) */}
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

      {/* Participación por Estamento (Bar) */}
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
  );
}
