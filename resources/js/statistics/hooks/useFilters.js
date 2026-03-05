import { useState, useCallback } from 'react';

// ---------------------------------------------------------------------------
// Helpers de fecha
// ---------------------------------------------------------------------------

export function today() {
  return new Date().toISOString().split('T')[0];
}

export function monthAgo() {
  const d = new Date();
  d.setMonth(d.getMonth() - 1);
  return d.toISOString().split('T')[0];
}

export function defaultFilters() {
  return { dateFrom: monthAgo(), dateTo: today() };
}

// ---------------------------------------------------------------------------
// Hook de filtros — estado único, auto-aplica al cambiar
// (no más pending/applied separados)
// ---------------------------------------------------------------------------

export function useFilters() {
  const [filters, setFilters] = useState(defaultFilters);

  /** Actualiza un campo (dateFrom | dateTo) — el useEffect del app re-fetcha automáticamente. */
  const updateFilter = useCallback((field, value) => {
    setFilters(prev => ({ ...prev, [field]: value }));
  }, []);

  /** Limpia un campo individual dejándolo vacío (sin límite de fecha). */
  const clearFilter = useCallback((field) => {
    setFilters(prev => ({ ...prev, [field]: '' }));
  }, []);

  /** Restablece ambos campos al rango por defecto (último mes). */
  const clearAll = useCallback(() => {
    setFilters(defaultFilters());
  }, []);

  return { filters, updateFilter, clearFilter, clearAll };
}
