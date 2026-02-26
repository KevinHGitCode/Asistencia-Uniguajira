import React, { useState, useCallback } from 'react';

// ── SVG icons ──────────────────────────────────────────────────────────────────

function IconCopy() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-3.5 h-3.5">
      <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
    </svg>
  );
}

function IconCheck() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5} strokeLinecap="round" strokeLinejoin="round" className="w-3.5 h-3.5">
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}

function IconTable() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-3.5 h-3.5">
      <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18" />
    </svg>
  );
}

function IconInfo() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round" className="w-3.5 h-3.5">
      <circle cx="12" cy="12" r="10" />
      <line x1="12" y1="16" x2="12" y2="12" />
      <line x1="12" y1="8" x2="12.01" y2="8" />
    </svg>
  );
}

// ── Image capture ─────────────────────────────────────────────────────────────

/**
 * Captures the chart's SVG + full container height (including HTML legend)
 * and copies it as a PNG to the clipboard (falls back to download).
 */
export async function captureAsImage(containerEl, title, isDark) {
  const svgEl = containerEl?.querySelector('svg');
  if (!svgEl) throw new Error('SVG no encontrado');

  // Use the container's full client dimensions to include the HTML legend area
  const containerW = containerEl.clientWidth  || 600;
  const containerH = containerEl.clientHeight || 300;

  // SVG position within the container
  const svgRect       = svgEl.getBoundingClientRect();
  const containerRect = containerEl.getBoundingClientRect();
  const svgOffsetX    = Math.round(svgRect.left - containerRect.left);
  const svgOffsetY    = Math.round(svgRect.top  - containerRect.top);
  const svgW          = svgEl.clientWidth  || containerW;
  const svgH          = svgEl.clientHeight || containerH;

  const PADDING  = 20;
  const TITLE_H  = title ? 36 : 0;
  const canvasW  = containerW + PADDING * 2;
  const canvasH  = containerH + PADDING * 2 + TITLE_H;
  const DPR      = window.devicePixelRatio || 1;

  const bg      = isDark ? '#18181b' : '#ffffff';
  const textClr = isDark ? '#9ca3af' : '#6b7280';

  // Clone SVG with explicit dimensions; allow overflow so rotated labels don't clip
  const clone = svgEl.cloneNode(true);
  clone.setAttribute('width',    svgW);
  clone.setAttribute('height',   svgH);
  clone.setAttribute('overflow', 'visible');
  clone.setAttribute('xmlns',    'http://www.w3.org/2000/svg');

  // Inline computed styles so fonts render correctly in off-screen canvas
  clone.querySelectorAll('text').forEach(t => {
    const cs = window.getComputedStyle(t);
    t.style.fontFamily = cs.fontFamily || 'system-ui, sans-serif';
    t.style.fontSize   = cs.fontSize   || '12px';
    if (!t.getAttribute('fill') && !t.style.fill) {
      t.style.fill = isDark ? '#9ca3af' : '#6b7280';
    }
  });

  const svgString = new XMLSerializer().serializeToString(clone);
  const svgBlob   = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
  const url       = URL.createObjectURL(svgBlob);

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
        ctx.fillStyle     = textClr;
        ctx.font          = '600 10px/1 system-ui, sans-serif';
        ctx.letterSpacing = '0.1em';
        ctx.fillText(title.toUpperCase(), PADDING, PADDING + 14);
      }

      // Chart SVG — positioned relative to the container
      ctx.drawImage(
        img,
        PADDING + svgOffsetX,
        PADDING + TITLE_H + svgOffsetY,
        svgW,
        svgH,
      );

      canvas.toBlob(async (blob) => {
        if (!blob) { reject(new Error('Canvas vacío')); return; }
        try {
          await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
          resolve('copied');
        } catch {
          const a    = document.createElement('a');
          a.href     = URL.createObjectURL(blob);
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
  const csv  = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\r\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `${title || 'datos'}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ── Icon button ───────────────────────────────────────────────────────────────

function IconBtn({ onClick, tooltip, children, active = false, state = 'idle', disabled = false }) {
  let cls = 'relative inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors duration-150 cursor-pointer select-none ';

  if (state === 'ok') {
    cls += 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30';
  } else if (state === 'err') {
    cls += 'text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30';
  } else if (state === 'working') {
    cls += 'text-gray-400 dark:text-zinc-500 cursor-wait';
  } else if (active) {
    cls += 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30';
  } else {
    cls += 'text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800';
  }

  return (
    <button
      type="button"
      title={tooltip}
      onClick={onClick}
      disabled={disabled || state === 'working'}
      className={cls}
    >
      {children}
    </button>
  );
}

// ── ChartToolbar ──────────────────────────────────────────────────────────────

/**
 * Compact icon-only toolbar, designed to sit in the card header row.
 *
 * Props:
 *  - chartRef : ref to the chart container div
 *  - title    : string
 *  - isDark   : bool
 *  - data     : array of { name, value }
 *  - modal    : 'data' | 'info' | null
 *  - onModal  : fn
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

  const copyTooltips = { idle: 'Copiar como imagen', working: 'Copiando…', ok: 'Copiado', err: 'Error al copiar' };

  return (
    <div className="flex items-center gap-0.5">
      <IconBtn
        onClick={handleCopy}
        tooltip={copyTooltips[copyState] ?? copyTooltips.idle}
        state={copyState}
      >
        {copyState === 'ok' ? <IconCheck /> : <IconCopy />}
      </IconBtn>

      <IconBtn
        onClick={() => onModal(modal === 'data' ? null : 'data')}
        tooltip="Ver datos"
        active={modal === 'data'}
      >
        <IconTable />
      </IconBtn>

      <IconBtn
        onClick={() => onModal(modal === 'info' ? null : 'info')}
        tooltip="Sobre este gráfico"
        active={modal === 'info'}
      >
        <IconInfo />
      </IconBtn>
    </div>
  );
}
