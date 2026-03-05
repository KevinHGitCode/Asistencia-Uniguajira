import { useState, useCallback, useRef } from 'react';

function buildQS(filters) {
  const p = new URLSearchParams();
  if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
  if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
  return p.toString();
}

const EMPTY = {
  counters: { events: null },
  charts: {
    topUsers:     [],
    eventsByRole: [],
    eventsByUser: [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para el módulo "Por Usuarios".
 * Todos los endpoints soportan filtros de fecha.
 */
export function useUsuariosStats() {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);

  const fetchAll = useCallback(async (filters) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    const qs  = buildQS(filters);
    const url = (path) => `/api/statistics${path}${qs ? '?' + qs : ''}`;

    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const [
        totalEvents,
        topUsers,
        eventsByRole,
        eventsByUser,
      ] = await Promise.all([
        fetch(url('/total-events'),   { signal }).then(r => r.json()),
        fetch(url('/top-users'),      { signal }).then(r => r.json()),
        fetch(url('/events-by-role'), { signal }).then(r => r.json()),
        fetch(url('/events-by-user'), { signal }).then(r => r.json()),
      ]);

      setState({
        counters: {
          events: totalEvents,
        },
        charts: {
          topUsers:     topUsers.map(d => ({ name: d.name, value: d.count })),
          eventsByRole: eventsByRole.map(d => ({ name: d.role, value: d.count })),
          eventsByUser: eventsByUser.map(d => ({ name: d.name, value: d.count })),
        },
        loading: false,
        error:   null,
      });
    } catch (err) {
      if (err.name === 'AbortError') return;
      setState(prev => ({ ...prev, loading: false, error: err.message }));
    }
  }, []);

  return { state, fetchAll };
}
