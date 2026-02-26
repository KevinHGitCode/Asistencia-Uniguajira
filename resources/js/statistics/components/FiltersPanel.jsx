import React from 'react';

const INPUT_CLASS = [
  'w-full rounded-lg text-sm px-3 py-2',
  'bg-white dark:bg-zinc-800',
  'border border-neutral-200 dark:border-neutral-700',
  'text-gray-900 dark:text-gray-100',
  'placeholder-gray-400 dark:placeholder-zinc-500',
  'focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400',
  'transition-colors duration-150',
].join(' ');

/**
 * Panel de filtros de estadísticas.
 *
 * Props:
 *  - filters  : { dateFrom, dateTo }
 *  - onChange  : (updater) => void   — recibe función de actualización de estado
 *  - onApply  : () => void
 *  - onClear  : () => void
 *  - loading  : bool
 */
export function FiltersPanel({ filters, onChange, onApply, onClear, loading }) {
  return (
    <div className="mb-6 p-4 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-base font-semibold text-gray-900 dark:text-gray-100">
          Filtros
        </h3>

        <div className="flex gap-2">
          <button
            type="button"
            onClick={onClear}
            className="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg transition-colors"
          >
            Limpiar filtros
          </button>

          <button
            type="button"
            onClick={onApply}
            disabled={loading}
            className="px-4 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed rounded-lg transition-colors"
          >
            {loading ? 'Cargando…' : 'Aplicar filtros'}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label className="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
            Fecha desde
          </label>
          <input
            type="date"
            value={filters.dateFrom}
            onChange={e => onChange(prev => ({ ...prev, dateFrom: e.target.value }))}
            className={INPUT_CLASS}
          />
        </div>

        <div>
          <label className="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
            Fecha hasta
          </label>
          <input
            type="date"
            value={filters.dateTo}
            onChange={e => onChange(prev => ({ ...prev, dateTo: e.target.value }))}
            className={INPUT_CLASS}
          />
        </div>
      </div>
    </div>
  );
}
