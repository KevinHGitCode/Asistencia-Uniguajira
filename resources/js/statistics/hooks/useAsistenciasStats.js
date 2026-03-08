import { useState, useCallback, useRef } from 'react';

function buildQS(filters, eventIds = null) {
  const p = new URLSearchParams();
  if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
  if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
  if (Array.isArray(eventIds)) {
    eventIds.forEach(id => p.append('eventIds[]', id));
  }
  return p.toString();
}

const EMPTY = {
  counters: { events: null, attendances: null, participants: null },
  charts: {
    attendancesByProgram: [],
    topEvents:            [],
    topParticipants:      [],
    byRole:               [],
    bySex:                [],
    byGroup:              [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para el módulo "Por Asistencias".
 * Un único request a /api/statistics/asistencias-summary devuelve
 * contadores + gráficos + demográficos.
 */
export function useAsistenciasStats() {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);

  const fetchAll = useCallback(async (filters, eventIds = null) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    const qs = buildQS(filters, eventIds);
    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const data = await fetch(
        `/api/statistics/asistencias-summary${qs ? '?' + qs : ''}`,
        { signal },
      ).then(r => r.json());

      setState({
        counters: {
          events:       data.counters.events,
          attendances:  data.counters.attendances,
          participants: data.counters.participants,
        },
        charts: {
          attendancesByProgram: data.charts.attendancesByProgram,
          topEvents:            data.charts.topEvents,
          topParticipants:      data.charts.topParticipants,
          byRole:               data.charts.byRole,
          bySex:                data.charts.bySex,
          byGroup:              data.charts.byGroup,
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
