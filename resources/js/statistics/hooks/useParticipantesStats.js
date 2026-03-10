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
  counters: { events: null, participants: null },
  charts: {
    participantsByProgram: [],
    byRole:                [],
    bySex:                 [],
    byGroup:               [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para el mÃƒÂ³dulo "Por Participantes".
 * Un ÃƒÂºnico request a /api/statistics/participantes-summary devuelve
 * contadores + grÃƒÂ¡ficos + demogrÃƒÂ¡ficos.
 */
export function useParticipantesStats() {
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
        `/api/statistics/participantes-summary${qs ? '?' + qs : ''}`,
        { signal },
      ).then(r => r.json());

      setState({
        counters: {
          events:       data.counters.events,
          participants: data.counters.participants,
        },
        charts: {
          participantsByProgram: data.charts.participantsByProgram,
          byRole:                data.charts.byRole,
          bySex:                 data.charts.bySex,
          byGroup:               data.charts.byGroup,
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
