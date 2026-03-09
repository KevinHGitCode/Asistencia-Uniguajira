import React from 'react';

// ---------------------------------------------------------------------------
// Ícono X para el botón de limpiar campo
// ---------------------------------------------------------------------------

const XIcon = () => (
  <svg viewBox="0 0 20 20" fill="currentColor" className="w-2.5 h-2.5" aria-hidden="true">
    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
  </svg>
);

// ---------------------------------------------------------------------------
// Input de fecha con botón X integrado
// ---------------------------------------------------------------------------

function DateField({ label, value, onChange, onClear }) {
  return (
    <div className="flex-1 min-w-[150px]">
      <label className="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
        {label}
      </label>
      <div className="flex items-center gap-1.5">
        <input
          type="date"
          value={value}
          onChange={e => onChange(e.target.value)}
          className={[
            'flex-1 rounded-lg text-sm px-3 py-1.5',
            'bg-white dark:bg-zinc-800',
            'border border-neutral-200 dark:border-neutral-700',
            'text-gray-900 dark:text-gray-100',
            'focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400',
            'transition-colors duration-150',
          ].join(' ')}
        />
        {/* X circular — visible solo cuando hay valor */}
        <button
          type="button"
          onClick={onClear}
          aria-label={`Limpiar ${label.toLowerCase()}`}
          style={{ visibility: value ? 'visible' : 'hidden' }}
          className="shrink-0 flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 dark:bg-zinc-600 text-gray-500 dark:text-gray-300 hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/40 dark:hover:text-red-400 transition-colors"
        >
          <XIcon />
        </button>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Panel de filtros
//
// Props:
//  - filters     : { dateFrom, dateTo }
//  - onUpdate    : (field, value) => void  — auto-aplica al cambiar
//  - onClear     : (field) => void         — limpia campo individual
//  - loading     : bool
//  - actions     : ReactNode|null          — slot para botones extra (p.ej. filtro de eventos)
// ---------------------------------------------------------------------------

export function FiltersPanel({ filters, onUpdate, onClear, loading, actions = null }) {
  return (
    <div className="mb-4 p-3 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm bg-white dark:bg-zinc-900">
      <div className="flex flex-wrap items-end gap-3">

        <DateField
          label="Fecha desde"
          value={filters.dateFrom}
          onChange={v => onUpdate('dateFrom', v)}
          onClear={() => onClear('dateFrom')}
        />

        <DateField
          label="Fecha hasta"
          value={filters.dateTo}
          onChange={v => onUpdate('dateTo', v)}
          onClear={() => onClear('dateTo')}
        />

        {/* Slot para acciones extra (p.ej. filtro de eventos) */}
        {actions && (
          <div className="self-end pb-0.5">
            {actions}
          </div>
        )}

        {/* Indicador de carga sutil */}
        <div
          className="flex items-center gap-1.5 text-xs text-gray-400 dark:text-zinc-500 pb-0.5 self-end transition-opacity duration-200"
          style={{ opacity: loading ? 1 : 0, pointerEvents: 'none' }}
        >
          <span className="w-3 h-3 border-2 border-gray-300 dark:border-zinc-600 border-t-blue-500 rounded-full animate-spin shrink-0" />
          Cargando…
        </div>

      </div>
    </div>
  );
}
