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
 * Carga en paralelo las 3 distribuciones demográficas.
 *
 * @param {object} options
 * @param {'participants'|'attendances'} options.mode
 *   - 'participants' (default): cuenta personas únicas con ≥1 asistencia.
 *     Usar en módulo "Por Participantes".
 *   - 'attendances': cuenta registros de asistencia (si alguien asistió 3 veces, suma 3).
 *     Usar en módulo "Por Asistencias".
 */
export function useDemografiaStats({ mode = 'participants' } = {}) {
  const [state, setState] = useState(EMPTY);
  const abortRef = useRef(null);
  // El prefijo determina qué endpoints se llaman (attendances-by-* vs participants-by-*)
  const prefix = mode === 'attendances' ? 'attendances' : 'participants';

  const fetchAll = useCallback(async (filters, eventIds = null) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    const qs  = buildQS(filters, eventIds);
    const url = (path) => `/api/statistics${path}${qs ? '?' + qs : ''}`;

    setState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const [byRole, bySex, byGroup] = await Promise.all([
        fetch(url(`/${prefix}-by-role`),  { signal }).then(r => r.json()),
        fetch(url(`/${prefix}-by-sex`),   { signal }).then(r => r.json()),
        fetch(url(`/${prefix}-by-group`), { signal }).then(r => r.json()),
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
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [prefix]);   // prefix deriva de mode, que es estático por instancia del hook

  return { state, fetchAll };
}
