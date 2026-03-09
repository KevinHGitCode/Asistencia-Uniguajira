import { useReducer, useCallback, useEffect } from 'react';

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

const initialState = {
  events:        [],
  total:         0,
  loading:       false,
  error:         null,
  // Opciones para los checklists
  filterOptions: { dependencies: [], users: [] },
  optionsLoaded: false,
};

function reducer(state, action) {
  switch (action.type) {
    case 'FETCH_START':
      return { ...state, loading: true, error: null };
    case 'FETCH_OK':
      return {
        ...state,
        loading: false,
        events:  action.payload.events ?? [],
        total:   action.payload.total  ?? 0,
      };
    case 'FETCH_ERR':
      return { ...state, loading: false, error: action.payload };
    case 'OPTIONS_OK':
      return { ...state, filterOptions: action.payload, optionsLoaded: true };
    default:
      return state;
  }
}

// ---------------------------------------------------------------------------
// Hook
// ---------------------------------------------------------------------------

export function useAdminEventos() {
  const [state, dispatch] = useReducer(reducer, initialState);

  // Cargar opciones de filtro una sola vez
  const fetchFilterOptions = useCallback(async () => {
    try {
      const res  = await fetch('/api/statistics/admin-eventos/filter-options');
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      dispatch({ type: 'OPTIONS_OK', payload: data });
    } catch (err) {
      console.error('[useAdminEventos] filter-options', err);
    }
  }, []);

  useEffect(() => {
    fetchFilterOptions();
  }, [fetchFilterOptions]);

  // Cargar eventos con filtros
  const fetchAll = useCallback(async (filters = {}) => {
    dispatch({ type: 'FETCH_START' });

    try {
      const params = new URLSearchParams();

      if (filters.from)   params.append('from',   filters.from);
      if (filters.to)     params.append('to',     filters.to);
      if (filters.search) params.append('search', filters.search);

      // Arrays: dependencies[]=1&dependencies[]=2
      if (filters.dependencies?.length) {
        filters.dependencies.forEach(id => params.append('dependencies[]', id));
      }
      if (filters.users?.length) {
        filters.users.forEach(id => params.append('users[]', id));
      }

      const res  = await fetch(`/api/statistics/admin-eventos?${params}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      dispatch({ type: 'FETCH_OK', payload: data });
    } catch (err) {
      console.error('[useAdminEventos]', err);
      dispatch({ type: 'FETCH_ERR', payload: err.message });
    }
  }, []);

  return { state, fetchAll };
}