import React, { useState, useCallback } from 'react';

// ── SVG icons (inline, no external deps) ──────────────────────────────────────

function IconCopy() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
      <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
    </svg>
  );
}

function IconCheck() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}

function IconTable() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
      <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18" />
    </svg>
  );
}

function IconInfo() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-4 h-4">
      <circle cx="12" cy="12" r="10" />
      <line x1="12" y1="16" x2="12" y2="12" />
      <line x1="12" y1="8" x2="12.01" y2="8" />
    </svg>
  );
}

// ── Image capture ─────────────────────────────────────────────────────────────

/**
 * Serializes the SVG inside `containerEl` to a PNG blob and copies it to the
 * clipboard (or falls back to downloading the file).
 */
export async function captureAsImage(containerEl, title, isDark) {
  const svgEl = containerEl?.querySelector('svg');
  if (!svgEl) throw new Error('SVG no encontrado');

  const { width, height } = svgEl.getBoundingClientRect();
  const PADDING    = 16;
  const TITLE_H    = title ? 32 : 0;
  const canvasW    = Math.round(width)  + PADDING * 2;
  const canvasH    = Math.round(height) + PADDING * 2 + TITLE_H;
  const DPR        = window.devicePixelRatio || 1;

  const bg     = isDark ? '#18181b' : '#ffffff';
  const textClr = isDark ? '#a1a1aa' : '#6b7280';

  // Clone SVG and set explicit dimensions
  const clone = svgEl.cloneNode(true);
  clone.setAttribute('width',  width);
  clone.setAttribute('height', height);
  clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

  // Inline computed styles for text elements (needed for off-screen rendering)
  clone.querySelectorAll('text').forEach(t => {
    const cs = window.getComputedStyle(t);
    t.style.fontFamily = cs.fontFamily || 'sans-serif';
    t.style.fontSize   = cs.fontSize   || '12px';
    t.style.fill       = cs.fill       || (isDark ? '#a1a1aa' : '#6b7280');
  });

  const svgString = new XMLSerializer().serializeToString(clone);
  const svgBlob   = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
  const url        = URL.createObjectURL(svgBlob);

  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = async () => {
      URL.revokeObjectURL(url);
      const canvas = document.createElement('canvas');
      canvas.width  = canvasW * DPR;
      canvas.height = canvasH * DPR;
      const ctx = canvas.getContext('2d');
      ctx.scale(DPR, DPR);

      // Background
      ctx.fillStyle = bg;
      ctx.fillRect(0, 0, canvasW, canvasH);

      // Title
      if (title) {
        ctx.fillStyle  = textClr;
        ctx.font       = '700 11px/1 system-ui, sans-serif';
        ctx.letterSpacing = '0.08em';
        ctx.fillText(title.toUpperCase(), PADDING, PADDING + 14);
      }

      // Chart
      ctx.drawImage(img, PADDING, PADDING + TITLE_H, width, height);

      canvas.toBlob(async (blob) => {
        if (!blob) { reject(new Error('Canvas vacío')); return; }
        try {
          await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
          resolve('copied');
        } catch {
          // Clipboard API not available → download instead
          const a  = document.createElement('a');
          a.href   = URL.createObjectURL(blob);
          a.download = `${title || 'grafico'}.png`;
          a.click();
          URL.revokeObjectURL(a.href);
          resolve('downloaded');
        }
      }, 'image/png');
    };
    img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Error al cargar SVG')); };
    img.src = url;
  });
}

// ── CSV download ──────────────────────────────────────────────────────────────

export function downloadCSV(data, title) {
  const total = data.reduce((s, r) => s + (r.value ?? 0), 0);
  const rows  = [
    ['Nombre', 'Valor', 'Porcentaje'],
    ...data.map(r => [
      r.name,
      r.value ?? 0,
      total > 0 ? ((r.value / total) * 100).toFixed(1) + '%' : '0%',
    ]),
    ['Total', total, '100%'],
  ];
  const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\r\n');
  // UTF-8 BOM so Excel opens it correctly
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `${title || 'datos'}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ── Toolbar button ────────────────────────────────────────────────────────────

function ToolBtn({ onClick, title, children, variant = 'idle' }) {
  const base = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors duration-150 select-none cursor-pointer';
  const variants = {
    idle    : 'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 hover:bg-gray-200 dark:hover:bg-zinc-700',
    working : 'bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500 cursor-wait',
    ok      : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
    err     : 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400',
    active  : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
  };
  return (
    <button
      type="button"
      title={title}
      onClick={onClick}
      disabled={variant === 'working'}
      className={`${base} ${variants[variant] ?? variants.idle}`}
    >
      {children}
    </button>
  );
}

// ── ChartToolbar ──────────────────────────────────────────────────────────────

/**
 * Props:
 *  - chartRef  : React ref pointing to the chart container div
 *  - title     : string — chart title (used in image + CSV filename)
 *  - isDark    : bool
 *  - data      : array of { name, value }
 *  - modal     : 'data' | 'info' | null — currently open modal
 *  - onModal   : (mode: string | null) => void — toggle modal
 */
export function ChartToolbar({ chartRef, title, isDark, data = [], modal, onModal }) {
  const [copyState, setCopyState] = useState('idle'); // idle | working | ok | err

  const handleCopy = useCallback(async () => {
    if (copyState === 'working') return;
    setCopyState('working');
    try {
      await captureAsImage(chartRef?.current, title, isDark);
      setCopyState('ok');
    } catch {
      setCopyState('err');
    } finally {
      setTimeout(() => setCopyState('idle'), 2500);
    }
  }, [chartRef, title, isDark, copyState]);

  const copyLabel = { idle: 'Copiar', working: 'Copiando…', ok: 'Copiado', err: 'Error' }[copyState];

  return (
    <div className="flex items-center gap-1.5 px-4 pb-2 pt-0 shrink-0 flex-wrap">
      {/* Copy as image */}
      <ToolBtn onClick={handleCopy} title="Copiar como imagen" variant={copyState}>
        {copyState === 'ok' ? <IconCheck /> : <IconCopy />}
        {copyLabel}
      </ToolBtn>

      {/* Data table */}
      <ToolBtn
        onClick={() => onModal(modal === 'data' ? null : 'data')}
        title="Ver datos del gráfico"
        variant={modal === 'data' ? 'active' : 'idle'}
      >
        <IconTable />
        Datos
      </ToolBtn>

      {/* Info / stats */}
      <ToolBtn
        onClick={() => onModal(modal === 'info' ? null : 'info')}
        title="Información descriptiva"
        variant={modal === 'info' ? 'active' : 'idle'}
      >
        <IconInfo />
        Info
      </ToolBtn>
    </div>
  );
}
