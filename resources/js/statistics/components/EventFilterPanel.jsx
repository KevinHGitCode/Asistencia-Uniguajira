import React from 'react';

// ── Íconos ────────────────────────────────────────────────────────────────────

const FunnelIcon = () => (
  <svg viewBox="0 0 20 20" fill="currentColor" className="w-4 h-4" aria-hidden="true">
    <path fillRule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.591L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clipRule="evenodd" />
  </svg>
);

// ── Botón disparador (colócalo en el slot actions del FiltersPanel) ───────────

/**
 * Botón sutil de embudo que abre/cierra el EventFilterPanel.
 *
 * Props:
 *  - open         : bool
 *  - onToggle     : () => void
 *  - isFiltered   : bool   — true cuando no están todos seleccionados
 *  - filteredCount: number — cuántos seleccionados
 *  - totalCount   : number — total en el período
 *  - loading      : bool
 */
export function EventFilterButton({ open, onToggle, isFiltered, filteredCount, totalCount, loading }) {
  return (
    <button
      type="button"
      onClick={onToggle}
      disabled={loading}
      title="Filtrar por eventos específicos"
      className={[
        'relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium',
        'border transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400',
        open || isFiltered
          ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-700'
          : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-neutral-200 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:text-gray-700 dark:hover:text-zinc-200',
        loading && 'opacity-50 cursor-not-allowed',
      ].join(' ')}
    >
      <FunnelIcon />
      <span className="hidden sm:inline">Eventos</span>

      {/* Badge con conteo cuando está filtrado */}
      {isFiltered && (
        <span className="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-[10px] font-semibold bg-blue-600 text-white leading-none">
          {filteredCount}/{totalCount}
        </span>
      )}
    </button>
  );
}

// ── Panel expandible de selección de eventos ──────────────────────────────────

/**
 * Panel desplegable que lista los eventos del período para selección.
 *
 * Props:
 *  - events        : array de { id, title, date, attendances_count }
 *  - loading       : bool
 *  - checkedIds    : Set<number>   — IDs chequeados actualmente
 *  - onToggle      : (id) => void
 *  - onSelectAll   : () => void
 *  - onClearAll    : () => void
 *  - isFiltered    : bool
 *  - filteredCount : number
 *  - totalCount    : number
 */
export function EventFilterPanel({
  events,
  loading,
  checkedIds,
  onToggle,
  onSelectAll,
  onClearAll,
  isFiltered,
  filteredCount,
  totalCount,
}) {
  return (
    <div className="mb-4 rounded-xl border border-blue-200 dark:border-blue-700/60 bg-blue-50/50 dark:bg-blue-900/10 shadow-sm overflow-hidden">

      {/* ── Cabecera ── */}
      <div className="flex items-center justify-between px-4 py-2.5 border-b border-blue-100 dark:border-blue-800/50">
        <div className="flex items-center gap-2 text-sm font-medium text-blue-700 dark:text-blue-300">
          <FunnelIcon />
          <span>
            {isFiltered
              ? `${filteredCount} de ${totalCount} evento${totalCount !== 1 ? 's' : ''} seleccionado${filteredCount !== 1 ? 's' : ''}`
              : `Todos los eventos (${totalCount})`
            }
          </span>
          {loading && (
            <span className="w-3.5 h-3.5 border-2 border-blue-300 border-t-blue-600 rounded-full animate-spin" />
          )}
        </div>

        <div className="flex items-center gap-1.5">
          <button
            type="button"
            onClick={onSelectAll}
            disabled={!isFiltered}
            className="text-xs px-2 py-1 rounded-md text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Todos
          </button>
          <button
            type="button"
            onClick={onClearAll}
            disabled={filteredCount === 0}
            className="text-xs px-2 py-1 rounded-md text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >
            Ninguno
          </button>
        </div>
      </div>

      {/* ── Lista de eventos ── */}
      <ul className="max-h-52 overflow-y-auto divide-y divide-blue-100 dark:divide-blue-800/40">
        {loading && events.length === 0 ? (
          <li className="px-4 py-3 text-sm text-gray-400 dark:text-zinc-500 italic">Cargando eventos…</li>
        ) : events.length === 0 ? (
          <li className="px-4 py-3 text-sm text-gray-400 dark:text-zinc-500 italic">
            No hay eventos en el período seleccionado.
          </li>
        ) : (
          events.map(ev => {
            const checked = checkedIds.has(ev.id);
            return (
              <li key={ev.id}>
                <label className="flex items-center gap-3 px-4 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors select-none">
                  <input
                    type="checkbox"
                    checked={checked}
                    onChange={() => onToggle(ev.id)}
                    className="w-3.5 h-3.5 rounded border-gray-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400 shrink-0"
                  />
                  <span className="flex-1 min-w-0">
                    <span className="block text-sm text-gray-800 dark:text-zinc-200 truncate font-medium">
                      {ev.title}
                    </span>
                    <span className="block text-xs text-gray-400 dark:text-zinc-500 mt-0.5">
                      {ev.date} · {ev.attendances_count} asistencia{ev.attendances_count !== 1 ? 's' : ''}
                    </span>
                  </span>
                </label>
              </li>
            );
          })
        )}
      </ul>
    </div>
  );
}
