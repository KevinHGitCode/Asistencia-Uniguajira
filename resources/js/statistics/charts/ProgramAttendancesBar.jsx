import React, { useRef, useEffect, useState } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, Cell, LabelList,
} from 'recharts';
import {
  getTheme, getTooltipStyle, getAxisTickStyle,
  truncate, getLabelAngle,
} from './utils.js';
import {
  BAR_PRIMARY_COLOR, CHART_DENSITY,
  CHART_ANIMATION, CHART_ANIMATION_DURATION,
  LABEL_MAX_CHARS,
} from '../config.js';
import { useMounted } from '../hooks/useMounted.js';

/** Tooltip personalizado que muestra el nombre completo del programa. */
function CustomTooltip({ active, payload, fullNames, isDark }) {
  if (!active || !payload?.length) return null;
  const t     = getTheme(isDark);
  const color = isDark ? BAR_PRIMARY_COLOR.dark : BAR_PRIMARY_COLOR.light;
  const idx   = payload[0]?.payload?._idx ?? 0;

  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4, maxWidth: 240, lineHeight: 1.3 }}>
        {fullNames[idx] ?? payload[0].name}
      </p>
      <p style={{ color }}>
        {payload[0].value.toLocaleString('es-CO')} asistencias
      </p>
    </div>
  );
}

// ── DOM-based dimming (bypasses React re-renders) ────────────────────────────

function useVertBarDimming(containerRef, mounted) {
  useEffect(() => {
    const el = containerRef.current;
    if (!el || !mounted) return;

    const handleOver = (e) => {
      if (e.target.closest('.recharts-tooltip-wrapper')) return;
      const rect = e.target.closest('.recharts-bar-rectangle');
      if (!rect) return;
      const hovered = rect.querySelector('path');
      el.querySelectorAll('.recharts-bar-rectangle path').forEach(p => {
        p.style.transition = 'fill-opacity 0.25s ease, filter 0.25s ease';
        if (p === hovered) {
          p.style.fillOpacity = '1';
          p.style.filter = 'brightness(1.12)';
        } else {
          p.style.fillOpacity = '0.35';
          p.style.filter = 'brightness(1)';
        }
      });
    };

    const handleLeave = () => {
      el.querySelectorAll('.recharts-bar-rectangle path').forEach(p => {
        p.style.fillOpacity = '1';
        p.style.filter = 'brightness(1)';
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
 * Grafico de barras verticales: Asistencias por Programa.
 */
export function ProgramAttendancesBar({ data, isDark }) {
  const mounted = useMounted();
  const containerRef = useRef(null);
  const [width, setWidth] = useState(600);

  useEffect(() => {
    const el = containerRef.current;
    if (!el) return;
    const ro = new ResizeObserver(([entry]) => setWidth(entry.contentRect.width));
    ro.observe(el);
    return () => ro.disconnect();
  }, []);

  const theme      = getTheme(isDark);
  const barColor   = isDark ? BAR_PRIMARY_COLOR.dark : BAR_PRIMARY_COLOR.light;
  const { angle, textAnchor, tickHeight } = getLabelAngle(data.length, width);
  const smallFont  = data.length >= CHART_DENSITY.barTinyAt;
  const maxChars   = angle !== 0 ? LABEL_MAX_CHARS.xAxis : LABEL_MAX_CHARS.xAxis + 4;

  const formatted = data.map((d, i) => ({
    ...d,
    shortName: truncate(d.name, maxChars),
    _idx:      i,
  }));

  const fullNames = data.map(d => d.name);

  useVertBarDimming(containerRef, mounted);

  if (!mounted) return <div className="w-full" style={{ height: '100%' }} />;

  return (
    <div ref={containerRef} className="w-full h-full min-w-0">
      <ResponsiveContainer width="100%" height="100%" minWidth={0}>
        <BarChart
          data={formatted}
          margin={{ top: 8, right: 16, bottom: tickHeight, left: 8 }}
        >
          <defs>
            <linearGradient id="barGradientVert" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%"   stopColor={barColor} stopOpacity={1}   />
              <stop offset="100%" stopColor={barColor} stopOpacity={0.5} />
            </linearGradient>
          </defs>
          <CartesianGrid
            strokeDasharray="4 4"
            stroke={theme.grid}
            vertical={false}
          />
          <XAxis
            dataKey="shortName"
            angle={angle}
            textAnchor={textAnchor}
            interval={0}
            tick={getAxisTickStyle(isDark, smallFont)}
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
            content={<CustomTooltip fullNames={fullNames} isDark={isDark} />}
            cursor={{ fill: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)' }}
          />
          <Bar
            dataKey="value"
            radius={[5, 5, 0, 0]}
            maxBarSize={52}
            fill="url(#barGradientVert)"
            isAnimationActive={CHART_ANIMATION}
            animationDuration={CHART_ANIMATION_DURATION}
            animationEasing="ease-out"
          >
            {formatted.map((_, i) => (
              <Cell
                key={i}
                fill="url(#barGradientVert)"
              />
            ))}
            <LabelList
              dataKey="value"
              position="top"
              formatter={v => v.toLocaleString('es-CO')}
              style={{ fill: theme.muted, fontSize: 11, fontWeight: 600 }}
            />
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}
