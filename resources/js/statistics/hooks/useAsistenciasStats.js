import { useState, useCallback, useRef } from 'react';

function buildQS(filters) {
  const p = new URLSearchParams();
  if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
  if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
  return p.toString();
}

const EMPTY = {
  counters: { events: null, attendances: null, participants: null },
  charts: {
    attendancesByProgram: [],
    topEvents:            [],
    topParticipants:      [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para el módulo "Por Asistencias".
 * Endpoints con filtros: totalEvents, totalAttendances, attendancesByProgram, topEvents.
 * Sin filtros:           totalParticipants, topParticipants.
 */
export function useAsistenciasStats() {
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
        totalAttendances,
        totalParticipants,
        attendancesByProgram,
        topEvents,
        topParticipants,
      ] = await Promise.all([
        fetch(url('/total-events'),                            { signal }).then(r => r.json()),
        fetch(url('/total-attendances'),                       { signal }).then(r => r.json()),
        fetch(url('/total-participants'),                       { signal }).then(r => r.json()),
        fetch(url('/attendances-by-program'),                  { signal }).then(r => r.json()),
        fetch(url('/top-events'),                              { signal }).then(r => r.json()),
        fetch(url('/top-participants'),                        { signal }).then(r => r.json()),
      ]);

      setState({
        counters: {
          events:       totalEvents,
          attendances:  totalAttendances,
          participants: totalParticipants,
        },
        charts: {
          attendancesByProgram: attendancesByProgram.map(d => ({ name: d.program, value: d.count })),
          topEvents:            topEvents.map(d => ({ name: d.title, value: d.count })),
          topParticipants:      topParticipants.map(d => ({ name: d.name,  value: d.count })),
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
