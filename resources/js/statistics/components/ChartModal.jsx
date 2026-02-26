import React, { useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';
import { downloadCSV } from './ChartToolbar.jsx';

// ── DataView ──────────────────────────────────────────────────────────────────

function DataView({ data, title }) {
  const total = data.reduce((s, r) => s + (r.value ?? 0), 0);

  return (
    <div className="flex flex-col gap-4">
      <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-700">
        <table className="w-full text-sm">
          <thead>
            <tr className="bg-gray-50 dark:bg-zinc-800/80 text-left">
              <th className="px-4 py-2.5 font-semibold text-gray-500 dark:text-zinc-400 w-full">Nombre</th>
              <th className="px-4 py-2.5 font-semibold text-gray-500 dark:text-zinc-400 text-right whitespace-nowrap">Valor</th>
              <th className="px-4 py-2.5 font-semibold text-gray-500 dark:text-zinc-400 text-right whitespace-nowrap">%</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-zinc-800">
            {data.map((row, i) => {
              const pct = total > 0 ? ((row.value / total) * 100).toFixed(1) : '0.0';
              return (
                <tr key={i} className="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                  <td className="px-4 py-2 text-gray-700 dark:text-zinc-300">{row.name}</td>
                  <td className="px-4 py-2 text-right font-mono text-gray-800 dark:text-zinc-200">
                    {(row.value ?? 0).toLocaleString('es-CO')}
                  </td>
                  <td className="px-4 py-2 text-right text-gray-500 dark:text-zinc-400">{pct}%</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr className="bg-gray-50 dark:bg-zinc-800/80 border-t-2 border-gray-200 dark:border-zinc-700">
              <td className="px-4 py-2 font-semibold text-gray-700 dark:text-zinc-300">Total</td>
              <td className="px-4 py-2 text-right font-mono font-semibold text-gray-800 dark:text-zinc-200">
                {total.toLocaleString('es-CO')}
              </td>
              <td className="px-4 py-2 text-right text-gray-500 dark:text-zinc-400">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <button
        type="button"
        onClick={() => downloadCSV(data, title)}
        className="self-start inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors border border-emerald-200 dark:border-emerald-800"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Descargar CSV
      </button>
    </div>
  );
}

// ── InfoView ──────────────────────────────────────────────────────────────────

function InfoView({ description }) {
  if (!description) {
    return (
      <p className="text-sm text-gray-400 dark:text-zinc-500 italic">
        Sin descripción disponible para este gráfico.
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {/* Icon + text */}
      <div className="flex gap-3">
        <div className="shrink-0 mt-0.5 w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-500 dark:text-blue-400">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="16" x2="12" y2="12" />
            <line x1="12" y1="8" x2="12.01" y2="8" />
          </svg>
        </div>
        <p className="text-sm leading-relaxed text-gray-600 dark:text-zinc-300">{description}</p>
      </div>

      {/* Hint */}
      <p className="text-xs text-gray-400 dark:text-zinc-500 border-t border-gray-100 dark:border-zinc-800 pt-3">
        Usa el botón <strong className="font-medium">Datos</strong> para ver los valores exactos y descargarlos en CSV.
      </p>
    </div>
  );
}

// ── Modal shell ───────────────────────────────────────────────────────────────

/**
 * Props:
 *  - isOpen      : bool
 *  - mode        : 'data' | 'info'
 *  - title       : string
 *  - description : string — chart description shown in 'info' mode
 *  - data        : array of { name, value }
 *  - onClose     : () => void
 */
export function ChartModal({ isOpen, mode, title, description, data = [], onClose }) {
  const handleKey = useCallback((e) => {
    if (e.key === 'Escape') onClose();
  }, [onClose]);

  useEffect(() => {
    if (!isOpen) return;
    document.addEventListener('keydown', handleKey);
    return () => document.removeEventListener('keydown', handleKey);
  }, [isOpen, handleKey]);

  if (!isOpen) return null;

  const heading = mode === 'data' ? `Datos — ${title}` : 'Sobre este gráfico';

  return createPortal(
    <div
      className="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
      role="dialog"
      aria-modal="true"
      aria-label={heading}
    >
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/40 backdrop-blur-[2px]"
        onClick={onClose}
      />

      {/* Panel */}
      <div className="relative z-10 w-full sm:max-w-lg max-h-[90dvh] flex flex-col bg-white dark:bg-zinc-900 sm:rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-700 overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800 shrink-0">
          <div>
            <p className="text-[10px] font-semibold tracking-widest uppercase text-gray-400 dark:text-zinc-500 mb-0.5">
              {title}
            </p>
            <h3 className="text-sm font-semibold text-gray-800 dark:text-zinc-100 leading-snug">
              {heading}
            </h3>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="shrink-0 ml-4 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors"
            aria-label="Cerrar"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        {/* Body */}
        <div className="overflow-y-auto p-5">
          {mode === 'data'
            ? <DataView data={data} title={title} />
            : <InfoView description={description} />
          }
        </div>
      </div>
    </div>,
    document.body,
  );
}
