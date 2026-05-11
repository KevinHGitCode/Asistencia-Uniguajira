import React, { useRef, useEffect } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, Cell, LabelList,
} from 'recharts';
import { getColors, getTheme, getTooltipStyle, truncate, getAxisTickStyle, groupTopN } from './utils.js';
import {
  CHART_ANIMATION, CHART_ANIMATION_DURATION, LABEL_MAX_CHARS, BAR_TOP_N,
} from '../config.js';
import { useMounted } from '../hooks/useMounted.js';

/** Tooltip que muestra el nombre completo. */
function CustomTooltip({ active, payload, fullNames, valueLabel, isDark }) {
  if (!active || !payload?.length) return null;
  const t   = getTheme(isDark);
  const idx = payload[0]?.payload?._idx ?? 0;

  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4, maxWidth: 260, lineHeight: 1.3 }}>
        {fullNames[idx] ?? payload[0].name}
      </p>
      <p style={{ color: payload[0].fill ?? '#cc5e50' }}>
        {payload[0].value.toLocaleString('es-CO')} {valueLabel}
      </p>
    </div>
  );
}

// ── DOM-based dimming (bypasses React re-renders) ────────────────────────────

function useBarDimming(containerRef, mounted) {
  useEffect(() => {
    const el = containerRef.current;
    if (!el || !mounted) return;

    const handleOver = (e) => {
      if (e.target.closest('.recharts-tooltip-wrapper')) return;
      const rect = e.target.closest('.recharts-bar-rectangle');
      if (!rect) return;
      const hovered = rect.querySelector('path');
      el.querySelectorAll('.recharts-bar-rectangle path').forEach(p => {
        p.style.transition = 'fill-opacity 0.25s ease';
        p.style.fillOpacity = p === hovered ? '1' : '0.35';
      });
    };

    const handleLeave = () => {
      el.querySelectorAll('.recharts-bar-rectangle path').forEach(p => {
        p.style.fillOpacity = '1';
      });
    };

    el.addEventListener('mouseover', handleOver);
    el.addEventListener('mouseleave', handleLeave);
    return () => {
      el.removeEventListener('mouseover', handleOver);
      el.removeEventListener('mouseleave', handleLeave);
    };
  }, [containerRef, mounted]);
}

/**
 * Grafico de barras horizontales reutilizable.
 */
export function TopHorizontalBar({ data, isDark, valueLabel = 'items', maxItems = BAR_TOP_N }) {
  const mounted      = useMounted();
  const containerRef = useRef(null);
  const theme        = getTheme(isDark);
  const colors       = getColors(isDark);

  const grouped   = groupTopN(data, maxItems);
  const fullNames = grouped.map(d => d.name);

  const formatted = grouped.map((d, i) => ({
    ...d,
    shortName: truncate(d.name, LABEL_MAX_CHARS.yAxisHoriz),
    _idx: i,
  }));

  useBarDimming(containerRef, mounted);

  if (!mounted) return <div className="w-full" style={{ height: '100%' }} />;

  return (
    <div ref={containerRef} className="w-full h-full min-w-0">
      <ResponsiveContainer width="100%" height="100%" minWidth={0}>
        <BarChart
          layout="vertical"
          data={formatted}
          margin={{ top: 4, right: 52, bottom: 4, left: 4 }}
        >
          <defs>
            {colors.slice(0, Math.min(formatted.length, colors.length)).map((color, i) => (
              <linearGradient key={i} id={`horizGrad${i}`} x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%"   stopColor={color} stopOpacity={0.55} />
                <stop offset="100%" stopColor={color} stopOpacity={1}    />
              </linearGradient>
            ))}
          </defs>
          <CartesianGrid
            strokeDasharray="4 4"
            stroke={theme.grid}
            horizontal={false}
          />
          <XAxis
            type="number"
            tick={getAxisTickStyle(isDark, true)}
            axisLine={false}
            tickLine={false}
            tickFormatter={v => Number.isInteger(v) ? v.toLocaleString('es-CO') : ''}
          />
          <YAxis
            type="category"
            dataKey="shortName"
            width={128}
            tick={{ fill: theme.text, fontSize: 12 }}
            axisLine={false}
            tickLine={false}
          />
          <Tooltip
            content={
              <CustomTooltip
                fullNames={fullNames}
                valueLabel={valueLabel}
                isDark={isDark}
              />
            }
            cursor={{ fill: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)' }}
          />
          <Bar
            dataKey="value"
            radius={[0, 5, 5, 0]}
            isAnimationActive={CHART_ANIMATION}
            animationDuration={CHART_ANIMATION_DURATION}
            animationEasing="ease-out"
          >
            {formatted.map((_, i) => (
              <Cell
                key={i}
                fill={`url(#horizGrad${i % colors.length})`}
              />
            ))}
            <LabelList
              dataKey="value"
              position="right"
              formatter={v => v.toLocaleString('es-CO')}
              style={{ fill: theme.muted, fontSize: 12, fontWeight: 500 }}
            />
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}
