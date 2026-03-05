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
// Hook compartido de filtros de fecha
// Gestiona estado "pendiente" (en el panel) vs "aplicado" (disparando fetch).
// ---------------------------------------------------------------------------

export function useFilters() {
  const [applied, setApplied] = useState(defaultFilters);
  const [pending, setPending] = useState(defaultFilters);

  const handleApply = useCallback(() => {
    setApplied({ ...pending });
  }, [pending]);

  const handleClear = useCallback(() => {
    const def = defaultFilters();
    setPending(def);
    setApplied(def);
  }, []);

  return { applied, pending, setPending, handleApply, handleClear };
}
