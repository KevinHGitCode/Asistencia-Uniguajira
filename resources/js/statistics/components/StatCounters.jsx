import React from 'react';

/** Ícono SVG del calendario */
const CalendarIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
  </svg>
);

/** Ícono SVG de usuarios */
const UsersIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
  </svg>
);

/** Ícono SVG de persona */
const PersonIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
  </svg>
);

/** Skeleton para el número cuando está cargando. */
function Skeleton() {
  return (
    <span className="block w-20 h-7 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
  );
}

/**
 * Tarjeta individual de contador.
 */
function Counter({ icon, value, label, colorBg, colorIcon }) {
  return (
    <div className="flex items-center gap-4 p-5 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm">
      <div className={`p-3 rounded-xl ${colorBg}`}>
        <span className={colorIcon}>{icon}</span>
      </div>
      <div>
        <p className="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
          {value === null ? <Skeleton /> : value.toLocaleString('es-CO')}
        </p>
        <p className="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{label}</p>
      </div>
    </div>
  );
}

/**
 * Sección de contadores globales (eventos, asistencias, participantes).
 */
export function StatCounters({ counters, loading }) {
  const v = (key) => (loading ? null : counters[key]);

  return (
    <section className="mb-8">
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Counter
          icon={<CalendarIcon />}
          value={v('events')}
          label="Número de Eventos"
          colorBg="bg-blue-100 dark:bg-blue-900/30"
          colorIcon="text-blue-600 dark:text-blue-400"
        />
        <Counter
          icon={<UsersIcon />}
          value={v('attendances')}
          label="Número de Asistencias"
          colorBg="bg-emerald-100 dark:bg-emerald-900/30"
          colorIcon="text-emerald-600 dark:text-emerald-400"
        />
        <Counter
          icon={<PersonIcon />}
          value={v('participants')}
          label="Participantes únicos"
          colorBg="bg-violet-100 dark:bg-violet-900/30"
          colorIcon="text-violet-600 dark:text-violet-400"
        />
      </div>
    </section>
  );
}
