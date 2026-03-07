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
  counters: { participants: null },
  charts: {
    participantsByProgram: [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para el módulo "Por Participantes".
 * Ambos endpoints soportan filtros de fecha (filtran por eventos.date).
 * Solo cuentan/muestran participantes con al menos una asistencia.
 */
export function useParticipantesStats() {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);

  const fetchAll = useCallback(async (filters, eventIds = null) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    const qs  = buildQS(filters, eventIds);
    const url = (path) => `/api/statistics${path}${qs ? '?' + qs : ''}`;

    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const [
        totalParticipants,
        participantsByProgram,
      ] = await Promise.all([
        fetch(url('/total-participants'),       { signal }).then(r => r.json()),
        fetch(url('/participants-by-program'),  { signal }).then(r => r.json()),
      ]);

      setState({
        counters: {
          participants: totalParticipants,
        },
        charts: {
          participantsByProgram: participantsByProgram.map(d => ({ name: d.program, value: d.count })),
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
