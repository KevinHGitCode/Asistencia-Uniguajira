import React, { useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';
import { downloadCSV } from './ChartToolbar.jsx';

// ── Stats computation ─────────────────────────────────────────────────────────

function computeStats(data) {
  if (!data || data.length === 0) return null;

  const values = data.map(d => d.value ?? 0).filter(v => !isNaN(v));
  if (values.length === 0) return null;

  const count  = values.length;
  const total  = values.reduce((s, v) => s + v, 0);
  const mean   = total / count;
  const sorted = [...values].sort((a, b) => a - b);
  const median = count % 2 === 0
    ? (sorted[count / 2 - 1] + sorted[count / 2]) / 2
    : sorted[Math.floor(count / 2)];
  const max    = sorted[count - 1];
  const min    = sorted[0];
  const stdDev = Math.sqrt(values.reduce((s, v) => s + (v - mean) ** 2, 0) / count);

  const maxItem = data.find(d => (d.value ?? 0) === max);
  const minItem = data.find(d => (d.value ?? 0) === min);

  return { count, total, mean, median, max, min, stdDev, maxItem, minItem };
}

function fmt(n) {
  if (n === undefined || n === null || isNaN(n)) return '—';
  return Number.isInteger(n) ? n.toLocaleString('es-CO') : n.toLocaleString('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
}

// ── Sub-views ─────────────────────────────────────────────────────────────────

function DataView({ data, title }) {
  const total = data.reduce((s, r) => s + (r.value ?? 0), 0);

  return (
    <div className="flex flex-col gap-4">
      <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-700">
        <table className="w-full text-sm">
          <thead>
            <tr className="bg-gray-50 dark:bg-zinc-800 text-left">
              <th className="px-4 py-2.5 font-semibold text-gray-600 dark:text-zinc-300 w-full">Nombre</th>
              <th className="px-4 py-2.5 font-semibold text-gray-600 dark:text-zinc-300 text-right whitespace-nowrap">Valor</th>
              <th className="px-4 py-2.5 font-semibold text-gray-600 dark:text-zinc-300 text-right whitespace-nowrap">%</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100 dark:divide-zinc-800">
            {data.map((row, i) => {
              const pct = total > 0 ? ((row.value / total) * 100).toFixed(1) : '0.0';
              return (
                <tr key={i} className="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
                  <td className="px-4 py-2 text-gray-700 dark:text-zinc-300">{row.name}</td>
                  <td className="px-4 py-2 text-right font-mono text-gray-800 dark:text-zinc-200">{fmt(row.value)}</td>
                  <td className="px-4 py-2 text-right text-gray-500 dark:text-zinc-400">{pct}%</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr className="bg-gray-50 dark:bg-zinc-800 border-t-2 border-gray-200 dark:border-zinc-700">
              <td className="px-4 py-2 font-semibold text-gray-700 dark:text-zinc-300">Total</td>
              <td className="px-4 py-2 text-right font-mono font-semibold text-gray-800 dark:text-zinc-200">{fmt(total)}</td>
              <td className="px-4 py-2 text-right text-gray-500 dark:text-zinc-400">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <button
        type="button"
        onClick={() => downloadCSV(data, title)}
        className="self-start inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/60 transition-colors"
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

function StatRow({ label, value }) {
  return (
    <div className="flex justify-between items-baseline gap-4 py-1.5 border-b border-gray-100 dark:border-zinc-800 last:border-0">
      <span className="text-sm text-gray-500 dark:text-zinc-400">{label}</span>
      <span className="text-sm font-semibold font-mono text-gray-800 dark:text-zinc-200 tabular-nums">{value}</span>
    </div>
  );
}

function InfoView({ data }) {
  const s = computeStats(data);
  if (!s) {
    return <p className="text-sm text-gray-400 dark:text-zinc-500">Sin datos suficientes para calcular estadísticas.</p>;
  }

  const insights = [];
  if (s.maxItem) insights.push(`🏆 "${s.maxItem.name}" lidera con ${fmt(s.max)} registros.`);
  if (s.count > 1 && s.stdDev > s.mean * 0.5) insights.push('📊 La distribución es bastante desigual entre categorías.');
  if (s.count > 1 && s.stdDev < s.mean * 0.15) insights.push('✅ Los valores están distribuidos de forma bastante uniforme.');
  if (s.minItem && s.min === 0) insights.push(`⚠️ "${s.minItem.name}" no registra actividad en el período.`);
  if (s.count >= 5 && s.maxItem) {
    const topPct = ((s.max / s.total) * 100).toFixed(1);
    if (parseFloat(topPct) >= 40) insights.push(`📌 "${s.maxItem.name}" concentra el ${topPct}% del total.`);
  }

  return (
    <div className="flex flex-col gap-5">
      {/* Numeric stats */}
      <div className="bg-gray-50 dark:bg-zinc-800 rounded-xl px-4 py-2">
        <StatRow label="Categorías"        value={s.count} />
        <StatRow label="Total"             value={fmt(s.total)} />
        <StatRow label="Promedio"          value={fmt(s.mean)} />
        <StatRow label="Mediana"           value={fmt(s.median)} />
        <StatRow label="Máximo"            value={`${fmt(s.max)} (${s.maxItem?.name ?? '—'})`} />
        <StatRow label="Mínimo"            value={`${fmt(s.min)} (${s.minItem?.name ?? '—'})`} />
        <StatRow label="Desv. estándar"    value={fmt(s.stdDev)} />
      </div>

      {/* Auto-generated insights */}
      {insights.length > 0 && (
        <div className="flex flex-col gap-2">
          <p className="text-[11px] font-semibold tracking-widest uppercase text-gray-400 dark:text-zinc-500">
            Observaciones
          </p>
          <ul className="flex flex-col gap-1.5">
            {insights.map((txt, i) => (
              <li key={i} className="text-sm text-gray-600 dark:text-zinc-300">{txt}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

// ── Modal shell ───────────────────────────────────────────────────────────────

/**
 * Props:
 *  - isOpen  : bool
 *  - mode    : 'data' | 'info'
 *  - title   : string
 *  - data    : array of { name, value }
 *  - onClose : () => void
 */
export function ChartModal({ isOpen, mode, title, data = [], onClose }) {
  // Close on Escape
  const handleKey = useCallback((e) => {
    if (e.key === 'Escape') onClose();
  }, [onClose]);

  useEffect(() => {
    if (!isOpen) return;
    document.addEventListener('keydown', handleKey);
    return () => document.removeEventListener('keydown', handleKey);
  }, [isOpen, handleKey]);

  if (!isOpen) return null;

  const heading = mode === 'data'
    ? `Datos — ${title}`
    : `Estadísticas — ${title}`;

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
          <h3 className="text-sm font-semibold text-gray-800 dark:text-zinc-100 pr-4 leading-snug">{heading}</h3>
          <button
            type="button"
            onClick={onClose}
            className="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 dark:text-zinc-500 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors"
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
            : <InfoView data={data} />
          }
        </div>
      </div>
    </div>,
    document.body,
  );
}
