import React, { useRef, useEffect, useState } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer,
} from 'recharts';
import {
  getColors, getTheme, getTooltipStyle, getAxisTickStyle,
  truncate, getLabelAngle,
} from './utils.js';
import {
  CHART_ANIMATION, CHART_ANIMATION_DURATION, LABEL_MAX_CHARS,
} from '../config.js';

// ── Tooltip ───────────────────────────────────────────────────────────────────

function CustomTooltip({ active, payload, label, isDark }) {
  if (!active || !payload?.length) return null;
  const t     = getTheme(isDark);
  const total = payload.reduce((s, p) => s + (p.value ?? 0), 0);

  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 6, fontSize: 12 }}>
        {label}
      </p>
      {payload.map((entry) => (
        <p key={entry.name} style={{ color: entry.fill, fontSize: 11 }}>
          {entry.name}: {entry.value.toLocaleString('es-CO')}
        </p>
      ))}
      {payload.length > 1 && (
        <p style={{
          color: t.muted, fontSize: 11,
          marginTop: 4, borderTop: `1px solid ${t.grid}`, paddingTop: 4,
        }}>
          Total: {total.toLocaleString('es-CO')}
        </p>
      )}
    </div>
  );
}

// ── Leyenda inline ────────────────────────────────────────────────────────────

function InlineLegend({ categories, colors, isDark }) {
  const t = getTheme(isDark);
  return (
    <div className="flex flex-wrap gap-x-4 gap-y-1 px-3 pb-1 pt-0.5 justify-center shrink-0">
      {categories.map((cat, i) => (
        <div key={cat} className="flex items-center gap-1.5">
          <div
            data-legend-dot={colors[i]}
            className="shrink-0 rounded-full"
            style={{ width: 8, height: 8, backgroundColor: colors[i] }}
          />
          <span
            data-legend-label=""
            className="text-[11px]"
            style={{ color: t.text }}
          >
            {cat}
          </span>
        </div>
      ))}
    </div>
  );
}

// ── Componente principal ──────────────────────────────────────────────────────

/**
 * Stacked bar chart: compara categorías demográficas entre eventos.
 *
 * Props:
 *  - data       : [{ name: 'Evento A', Masculino: 30, Femenino: 45, ... }]
 *  - categories : ['Masculino', 'Femenino', ...]  — orden y keys del data
 *  - isDark     : bool
 */
export function StackedCompareBar({ data, categories, isDark }) {
  const containerRef = useRef(null);
  const [width, setWidth] = useState(600);

  useEffect(() => {
    const el = containerRef.current;
    if (!el) return;
    const ro = new ResizeObserver(([entry]) => setWidth(entry.contentRect.width));
    ro.observe(el);
    return () => ro.disconnect();
  }, []);

  const theme  = getTheme(isDark);
  const colors = getColors(isDark);

  const { angle, textAnchor, tickHeight } = getLabelAngle(data.length, width);
  const maxChars  = angle !== 0 ? LABEL_MAX_CHARS.xAxis : LABEL_MAX_CHARS.xAxis + 4;

  const formatted = data.map(d => ({
    ...d,
    _label: truncate(d.name, maxChars),
  }));

  const barColors = categories.map((_, i) => colors[i % colors.length]);

  return (
    <div ref={containerRef} className="w-full h-full flex flex-col">
      <div className="flex-1 min-h-0">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart
            data={formatted}
            margin={{ top: 10, right: 16, bottom: tickHeight, left: 8 }}
          >
            <CartesianGrid strokeDasharray="4 4" stroke={theme.grid} vertical={false} />
            <XAxis
              dataKey="_label"
              angle={angle}
              textAnchor={textAnchor}
              interval={0}
              tick={getAxisTickStyle(isDark, true)}
              axisLine={{ stroke: theme.axis }}
              tickLine={false}
              height={tickHeight}
              dy={angle !== 0 ? 4 : 0}
            />
            <YAxis
              tick={getAxisTickStyle(isDark, true)}
              axisLine={false}
              tickLine={false}
              tickFormatter={v => Number.isInteger(v) ? v.toLocaleString('es-CO') : ''}
            />
            <Tooltip
              content={<CustomTooltip isDark={isDark} />}
              cursor={{ fill: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)' }}
            />
            {categories.map((cat, i) => (
              <Bar
                key={cat}
                dataKey={cat}
                name={cat}
                stackId="a"
                fill={barColors[i]}
                isAnimationActive={CHART_ANIMATION}
                animationDuration={CHART_ANIMATION_DURATION}
                animationEasing="ease-out"
                radius={i === categories.length - 1 ? [4, 4, 0, 0] : [0, 0, 0, 0]}
              />
            ))}
          </BarChart>
        </ResponsiveContainer>
      </div>
      <InlineLegend categories={categories} colors={barColors} isDark={isDark} />
    </div>
  );
}
