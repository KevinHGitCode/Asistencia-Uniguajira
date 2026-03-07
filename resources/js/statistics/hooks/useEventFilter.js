import { useState, useCallback, useRef, useEffect } from 'react';

/**
 * Hook para seleccionar eventos específicos dentro del período de fechas.
 *
 * Comportamiento:
 *  - Por defecto todos los eventos del período están "seleccionados" (sin filtro).
 *  - Cuando el usuario desmarca algún evento, se activa el filtro.
 *  - Al cambiar las fechas (filters), la lista de eventos se recarga
 *    y la selección vuelve al estado "todos".
 *
 * Retorna:
 *  - events        : array de { id, title, date, attendances_count }
 *  - evLoading     : bool
 *  - open          : bool — panel visible
 *  - setOpen       : fn
 *  - selectedIds   : Set<number>  — IDs actualmente seleccionados
 *  - toggle        : (id) => void
 *  - selectAll     : () => void
 *  - clearAll      : () => void
 *  - isFiltered    : bool — true si NO están todos seleccionados
 *  - filteredCount : number — cuántos están seleccionados cuando isFiltered
 *  - totalCount    : number — total de eventos en el período
 *  - effectiveEventIds : null | number[]
 *      null  → sin filtro (todos seleccionados o lista vacía)
 *      array → solo estos IDs
 */
export function useEventFilter(filters) {
  const [events,      setEvents]      = useState([]);
  const [evLoading,   setEvLoading]   = useState(false);
  const [open,        setOpen]        = useState(false);
  // null = "todos seleccionados" (estado inicial y tras reset)
  const [selectedIds, setSelectedIds] = useState(null);
  const abortRef = useRef(null);

  // ── Carga de eventos ────────────────────────────────────────────────────────

  const fetchEvents = useCallback(async (f) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    setEvLoading(true);

    try {
      const p = new URLSearchParams();
      if (f.dateFrom) p.append('dateFrom', f.dateFrom);
      if (f.dateTo)   p.append('dateTo',   f.dateTo);
      const qs = p.toString();

      const data = await fetch(
        `/api/statistics/compare/events${qs ? '?' + qs : ''}`,
        { signal },
      ).then(r => r.json());

      setEvents(Array.isArray(data) ? data : []);
      // Resetear selección a "todos" cuando cambia el período
      setSelectedIds(null);
    } catch (err) {
      if (err.name === 'AbortError') return;
      setEvents([]);
    } finally {
      setEvLoading(false);
    }
  }, []);

  // Recargar cuando cambien los filtros de fecha
  useEffect(() => {
    fetchEvents(filters);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters.dateFrom, filters.dateTo, fetchEvents]);

  // ── Acciones de selección ───────────────────────────────────────────────────

  // Convierte selectedIds (null = todos) a un Set real usando la lista actual
  const resolveSet = useCallback((ids) => {
    if (ids === null) return new Set(events.map(e => e.id));
    return ids;
  }, [events]);

  const toggle = useCallback((id) => {
    setSelectedIds(prev => {
      const set = new Set(resolveSet(prev));
      if (set.has(id)) set.delete(id);
      else             set.add(id);
      // Si vuelven a estar todos marcados, volvemos al estado "null" (sin filtro)
      if (set.size === events.length) return null;
      return set;
    });
  }, [resolveSet, events.length]);

  const selectAll = useCallback(() => {
    setSelectedIds(null); // null = todos
  }, []);

  const clearAll = useCallback(() => {
    setSelectedIds(new Set());
  }, []);

  // ── Valores derivados ───────────────────────────────────────────────────────

  const totalCount    = events.length;
  const isFiltered    = selectedIds !== null; // null = todos = sin filtro
  const filteredCount = isFiltered ? selectedIds.size : totalCount;

  // Lo que realmente se envía a la API:
  //  - null  → sin parámetro eventIds (todos)
  //  - []    → sin resultados (ninguno seleccionado)
  //  - [id…] → solo esos
  const effectiveEventIds = isFiltered ? [...selectedIds] : null;

  // Para el panel: qué IDs están chequeados
  const checkedIds = isFiltered ? selectedIds : new Set(events.map(e => e.id));

  return {
    events,
    evLoading,
    open,
    setOpen,
    checkedIds,
    toggle,
    selectAll,
    clearAll,
    isFiltered,
    filteredCount,
    totalCount,
    effectiveEventIds,
  };
}
