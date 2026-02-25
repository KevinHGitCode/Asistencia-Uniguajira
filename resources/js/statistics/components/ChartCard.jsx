import React from 'react';

function LoadingSpinner() {
  return (
    <div className="flex items-center justify-center h-full">
      <div className="w-8 h-8 border-[3px] border-blue-500 dark:border-blue-400 border-t-transparent rounded-full animate-spin" />
    </div>
  );
}

function EmptyState() {
  return (
    <div className="flex flex-col items-center justify-center h-full gap-3 text-gray-300 dark:text-zinc-600 select-none">
      <svg
        className="w-12 h-12"
        fill="none"
        stroke="currentColor"
        strokeWidth={1.25}
        viewBox="0 0 24 24"
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
        />
      </svg>
      <span className="text-sm font-medium text-gray-400 dark:text-zinc-500">
        Sin datos en el período seleccionado
      </span>
    </div>
  );
}

/**
 * Tarjeta contenedora para un gráfico.
 *
 * Props:
 *  - title   : string  — etiqueta superior (opcional)
 *  - height  : number  — altura del área del gráfico en px (default: 340)
 *  - loading : bool
 *  - isEmpty : bool
 *  - children: nodo React (el componente de gráfico)
 */
export function ChartCard({ title, height = 340, loading = false, isEmpty = false, children }) {
  return (
    <div className="flex flex-col bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
      {title && (
        <div className="px-5 pt-5 pb-0 shrink-0">
          <p className="text-[11px] font-semibold tracking-widest uppercase text-gray-400 dark:text-zinc-500">
            {title}
          </p>
        </div>
      )}

      <div style={{ height }} className="relative px-2 pb-3 pt-2">
        {loading  ? <LoadingSpinner /> :
         isEmpty  ? <EmptyState />    :
                    children}
      </div>
    </div>
  );
}
