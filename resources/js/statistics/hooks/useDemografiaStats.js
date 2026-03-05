import { useState, useCallback, useRef } from 'react';

function buildQS(filters) {
  const p = new URLSearchParams();
  if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
  if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
  return p.toString();
}

const EMPTY = {
  charts: {
    byRole:  [],
    bySex:   [],
    byGroup: [],
  },
  loading: true,
  error:   null,
};

/**
 * Datos para la sección "Perfil Demográfico".
 * Carga en paralelo las 3 distribuciones demográficas de participantes.
 * Solo cuenta participantes con al menos una asistencia en el período.
 */
export function useDemografiaStats() {
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
      const [byRole, bySex, byGroup] = await Promise.all([
        fetch(url('/participants-by-role'),  { signal }).then(r => r.json()),
        fetch(url('/participants-by-sex'),   { signal }).then(r => r.json()),
        fetch(url('/participants-by-group'), { signal }).then(r => r.json()),
      ]);

      setState({
        charts: {
          byRole:  byRole.map(d  => ({ name: d.label, value: d.count })),
          bySex:   bySex.map(d   => ({ name: d.label, value: d.count })),
          byGroup: byGroup.map(d => ({ name: d.label, value: d.count })),
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
