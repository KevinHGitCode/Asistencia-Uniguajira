import React, { useState, useEffect } from 'react';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';
import { ProgramParticipantsBar } from './charts/ProgramParticipantsBar.jsx';
import { RoleParticipantsPie } from './charts/RoleParticipantsPie.jsx';
import { RoleParticipantsBar } from './charts/RoleParticipantsBar.jsx';

function ChartCard({ title, children, onCopy, isDark }) {
  const [copied, setCopied] = useState(false);

  const handleCopy = () => {
    onCopy?.();
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="relative">
      <div className="flex items-center justify-between mb-2">
        <h3 className="text-lg font-medium">{title}</h3>
        <div className="flex gap-1">
          <button
            type="button"
            onClick={handleCopy}
            className="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
            title="Copiar"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
          <button
            type="button"
            className="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
            title="Descargar datos"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </button>
          <button
            type="button"
            className="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
            title="Información"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        </div>
      </div>
      <div className="relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700">
        {children}
      </div>
    </div>
  );
}

export default function EventChartsApp({ eventId }) {
  const [isDark, setIsDark] = useState(false);
  const [programData, setProgramData] = useState([]);
  const [roleData, setRoleData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Detect dark mode
  useEffect(() => {
    const dark = document.documentElement.classList.contains('dark');
    setIsDark(dark);

    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.classList.contains('dark'));
    });

    observer.observe(document.documentElement, {
      attributes: true,
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
        setProgramData(
          programs.map(item => ({
            name: item.program,
            value: item.count,
          }))
        );
        setRoleData(
          roles.map(item => ({
            name: item.role,
            value: item.count,
          }))
        );
      })
      .catch(err => {
        setError(err.message);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [eventId]);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center text-gray-500 dark:text-gray-400">
          <div className="animate-spin inline-block w-8 h-8 border-4 border-current border-r-transparent rounded-full mb-4"></div>
          <p>Cargando gráficos...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center text-red-500">
          <p>Error al cargar los gráficos: {error}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {/* Program Pie */}
      <ChartCard
        title="Distribución por Programa"
        isDark={isDark}
        onCopy={() => {
          const text = programData.map(d => `${d.name}: ${d.value}`).join('\n');
          navigator.clipboard.writeText(text);
        }}
      >
        <ProgramParticipantsPie data={programData} isDark={isDark} />
      </ChartCard>

      {/* Program Bar */}
      <ChartCard
        title="Participación por Programa"
        isDark={isDark}
        onCopy={() => {
          const text = programData.map(d => `${d.name}: ${d.value}`).join('\n');
          navigator.clipboard.writeText(text);
        }}
      >
        <ProgramParticipantsBar data={programData} isDark={isDark} />
      </ChartCard>

      {/* Role Pie */}
      <ChartCard
        title="Distribución por Rol"
        isDark={isDark}
        onCopy={() => {
          const text = roleData.map(d => `${d.name}: ${d.value}`).join('\n');
          navigator.clipboard.writeText(text);
        }}
      >
        <RoleParticipantsPie data={roleData} isDark={isDark} />
      </ChartCard>

      {/* Role Bar */}
      <ChartCard
        title="Participación por Rol"
        isDark={isDark}
        onCopy={() => {
          const text = roleData.map(d => `${d.name}: ${d.value}`).join('\n');
          navigator.clipboard.writeText(text);
        }}
      >
        <RoleParticipantsBar data={roleData} isDark={isDark} />
      </ChartCard>
    </div>
  );
}
