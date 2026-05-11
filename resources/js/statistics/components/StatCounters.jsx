import React, { useState, useEffect, useRef, useCallback } from 'react';

/** Icono SVG del calendario */
const CalendarIcon = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
  </svg>
);

/** Icono SVG de usuarios */
const UsersIcon = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
  </svg>
);

/** Icono SVG de persona */
const PersonIcon = () => (
  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round"
      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
  </svg>
);

/** Skeleton para el numero cuando esta cargando. */
function Skeleton() {
  return (
    <span className="block w-14 h-5 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
  );
}

// ── Count-up animation hook ──────────────────────────────────────────────────

const COUNTUP_DURATION = 1200;
const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

function useCountUp(target, duration = COUNTUP_DURATION) {
  const [display, setDisplay] = useState(target ?? 0);
  const rafRef   = useRef(null);
  const startRef = useRef(null);
  const fromRef  = useRef(0);

  const animate = useCallback((from, to, dur) => {
    if (rafRef.current) cancelAnimationFrame(rafRef.current);
    fromRef.current  = from;
    startRef.current = null;

    const step = (timestamp) => {
      if (startRef.current === null) startRef.current = timestamp;
      const elapsed  = timestamp - startRef.current;
      const progress = Math.min(elapsed / dur, 1);
      const eased    = easeOutCubic(progress);
      const current  = Math.round(from + (to - from) * eased);
      setDisplay(current);

      if (progress < 1) {
        rafRef.current = requestAnimationFrame(step);
      } else {
        rafRef.current = null;
      }
    };

    rafRef.current = requestAnimationFrame(step);
  }, []);

  useEffect(() => {
    if (target === null || target === undefined) return;
    animate(fromRef.current, target, duration);
    return () => { if (rafRef.current) cancelAnimationFrame(rafRef.current); };
  }, [target, duration, animate]);

  return target === null || target === undefined ? null : display;
}

/**
 * Tarjeta compacta de contador.
 */
function Counter({ icon, value, label, colorBg, colorIcon }) {
  const animated = useCountUp(value);

  return (
    <div className="flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
      <div className={`p-2 rounded-lg ${colorBg} shrink-0`}>
        <span className={colorIcon}>{icon}</span>
      </div>
      <div>
        <p className="text-lg font-bold text-gray-900 dark:text-gray-100 leading-tight">
          {animated === null ? <Skeleton /> : animated.toLocaleString('es-CO')}
        </p>
        <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{label}</p>
      </div>
    </div>
  );
}

/**
 * Seccion de contadores globales (eventos, asistencias, participantes).
 */
export function StatCounters({ counters, loading }) {
  const v = (key) => (loading ? null : counters[key]);

  return (
    <section className="mb-5">
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <Counter
          icon={<CalendarIcon />}
          value={v('events')}
          label="Número de Eventos"
          colorBg="bg-[#cc5e50]/10 dark:bg-[#cc5e50]/20"
          colorIcon="text-[#cc5e50]"
        />
        <Counter
          icon={<UsersIcon />}
          value={v('attendances')}
          label="Número de Asistencias"
          colorBg="bg-[#e2a542]/10 dark:bg-[#e2a542]/20"
          colorIcon="text-[#e2a542]"
        />
        <Counter
          icon={<PersonIcon />}
          value={v('participants')}
          label="Participantes únicos"
          colorBg="bg-[#62a9b6]/10 dark:bg-[#62a9b6]/20"
          colorIcon="text-[#62a9b6]"
        />
      </div>
    </section>
  );
}
