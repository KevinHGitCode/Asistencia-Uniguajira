import { useState, useCallback, useRef, useEffect } from 'react';

/**
 * Datos del listado de participantes (ADR-0008).
 * - fetchList: trae una página filtrada de /api/participants (con AbortController).
 * - options: catálogos para los selects de filtro (se cargan una vez).
 */
export function useParticipants() {
    const [state, setState] = useState({ data: [], meta: null, loading: true, error: null });
    const [options, setOptions] = useState({ types: [], programs: [], dependencies: [], affiliations: [] });
    const abortRef = useRef(null);

    const fetchOptions = useCallback(async () => {
        try {
            const res = await fetch('/api/participants/filter-options', {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            setOptions(await res.json());
        } catch (err) {
            console.error('[useParticipants] filter-options', err);
        }
    }, []);

    const fetchList = useCallback(async (params) => {
        if (abortRef.current) abortRef.current.abort();
        abortRef.current = new AbortController();
        const { signal } = abortRef.current;

        setState((prev) => ({ ...prev, loading: true, error: null }));

        try {
            const qs = new URLSearchParams();
            Object.entries(params).forEach(([key, value]) => {
                if (value === '' || value === null || value === undefined || value === false) return;
                qs.append(key, value === true ? '1' : value);
            });

            const res = await fetch(`/api/participants?${qs}`, {
                signal,
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const json = await res.json();
            setState({ data: json.data ?? [], meta: json.meta ?? null, loading: false, error: null });
        } catch (err) {
            if (err.name === 'AbortError') return; // cancelada al cambiar filtros
            console.error('[useParticipants]', err);
            setState((prev) => ({ ...prev, loading: false, error: err.message }));
        }
    }, []);

    useEffect(() => {
        fetchOptions();
    }, [fetchOptions]);

    return { ...state, options, fetchList };
}
