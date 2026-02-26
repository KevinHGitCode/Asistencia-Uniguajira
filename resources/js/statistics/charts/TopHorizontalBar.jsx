import React from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, Cell, LabelList,
} from 'recharts';
import { getColors, getTheme, getTooltipStyle, truncate, getAxisTickStyle } from './utils.js';
import {
  CHART_ANIMATION, CHART_ANIMATION_DURATION, LABEL_MAX_CHARS,
} from '../config.js';

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
      <p style={{ color: payload[0].fill ?? '#60a5fa' }}>
        {payload[0].value.toLocaleString('es-CO')} {valueLabel}
      </p>
    </div>
  );
}

/**
 * Gráfico de barras horizontales reutilizable.
 * Usado para: Top Eventos, Top Participantes, Top Usuarios, Eventos por Usuario.
 *
 * Props:
 *  - data       : { name, value }[]
 *  - isDark     : bool
 *  - valueLabel : string  — texto después del número en el tooltip (ej. "asistencias")
 */
export function TopHorizontalBar({ data, isDark, valueLabel = 'ítems' }) {
  const theme    = getTheme(isDark);
  const colors   = getColors(isDark);
  const fullNames = data.map(d => d.name);

  const formatted = data.map((d, i) => ({
    ...d,
    shortName: truncate(d.name, LABEL_MAX_CHARS.yAxisHoriz),
    _idx: i,
  }));

  return (
    <div className="w-full h-full">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart
          layout="vertical"
          data={formatted}
          margin={{ top: 4, right: 52, bottom: 4, left: 4 }}
        >
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
              <Cell key={i} fill={colors[i % colors.length]} />
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
