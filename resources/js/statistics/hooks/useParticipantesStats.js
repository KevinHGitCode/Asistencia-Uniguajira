import { useState, useCallback, useRef } from 'react';

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
 * Ninguno de estos endpoints soporta filtros de fecha.
 */
export function useParticipantesStats() {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);

  const fetchAll = useCallback(async () => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const [
        totalParticipants,
        participantsByProgram,
      ] = await Promise.all([
        fetch('/api/statistics/total-participants',   { signal }).then(r => r.json()),
        fetch('/api/statistics/participants-by-program', { signal }).then(r => r.json()),
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
