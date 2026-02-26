import { useState, useCallback, useRef } from 'react';

/**
 * Convierte los filtros en una query string para la API.
 */
function buildQS(filters) {
  const p = new URLSearchParams();
  if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
  if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
  return p.toString();
}

/** Estado vacío inicial. */
const EMPTY = {
  counters: { events: null, attendances: null, participants: null },
  charts: {
    attendancesByProgram:  [],
    participantsByProgram: [],
    topEvents:             [],
    topParticipants:       [],
    topUsers:              [],
    eventsByRole:          [],
    eventsByUser:          [],
  },
  loading: true,
  error:   null,
};

/**
 * Hook central de datos.
 * Realiza todas las peticiones en paralelo con Promise.all y cancelación
 * via AbortController cuando los filtros cambian antes de que terminen.
 */
export function useStatistics() {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);

  const fetchAll = useCallback(async (filters) => {
    // Cancelar peticiones anteriores
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    const qs  = buildQS(filters);
    const url = (path) => `/api/statistics${path}${qs ? '?' + qs : ''}`;

    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const [
        totalEvents,
        totalAttendances,
        totalParticipants,
        attendancesByProgram,
        participantsByProgram,
        topEvents,
        topParticipants,
        topUsers,
        eventsByRole,
        eventsByUser,
      ] = await Promise.all([
        fetch(url('/total-events'),            { signal }).then(r => r.json()),
        fetch(url('/total-attendances'),        { signal }).then(r => r.json()),
        fetch('/api/statistics/total-participants', { signal }).then(r => r.json()),
        fetch(url('/attendances-by-program'),   { signal }).then(r => r.json()),
        fetch('/api/statistics/participants-by-program', { signal }).then(r => r.json()),
        fetch(url('/top-events'),               { signal }).then(r => r.json()),
        fetch('/api/statistics/top-participants', { signal }).then(r => r.json()),
        fetch(url('/top-users'),                { signal }).then(r => r.json()),
        fetch(url('/events-by-role'),           { signal }).then(r => r.json()),
        fetch(url('/events-by-user'),           { signal }).then(r => r.json()),
      ]);

      setState({
        counters: {
          events:       totalEvents,
          attendances:  totalAttendances,
          participants: totalParticipants,
        },
        charts: {
          attendancesByProgram:  attendancesByProgram.map(d => ({ name: d.program, value: d.count })),
          participantsByProgram: participantsByProgram.map(d => ({ name: d.program, value: d.count })),
          topEvents:             topEvents.map(d => ({ name: d.title,  value: d.count })),
          topParticipants:       topParticipants.map(d => ({ name: d.name, value: d.count })),
          topUsers:              topUsers.map(d => ({ name: d.name, value: d.count })),
          eventsByRole:          eventsByRole.map(d => ({ name: d.role, value: d.count })),
          eventsByUser:          eventsByUser.map(d => ({ name: d.name, value: d.count })),
        },
        loading: false,
        error:   null,
      });
    } catch (err) {
      if (err.name === 'AbortError') return; // petición cancelada intencionalmente
      setState(prev => ({ ...prev, loading: false, error: err.message }));
    }
  }, []);

  return { state, fetchAll };
}
