import React from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid,
  Tooltip, ResponsiveContainer, Cell, LabelList,
} from 'recharts';
import { getColors, getTheme, getTooltipStyle, getAxisTickStyle } from './utils.js';
import { CHART_ANIMATION, CHART_ANIMATION_DURATION } from '../config.js';

/** Mapeo de roles internos a etiquetas legibles. */
const ROLE_LABELS = {
  admin:     'Administrador',
  user:      'Usuario',
  moderator: 'Moderador',
};

function CustomTooltip({ active, payload, isDark }) {
  if (!active || !payload?.length) return null;
  const t = getTheme(isDark);

  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4 }}>
        {payload[0].payload.label}
      </p>
      <p style={{ color: payload[0].fill }}>
        {payload[0].value.toLocaleString('es-CO')} eventos creados
      </p>
    </div>
  );
}

/**
 * Gráfico de barras verticales: Eventos creados por rol de usuario.
 * Generalmente tiene 2-3 barras (admin / user).
 */
export function EventsByRoleChart({ data, isDark }) {
  const theme  = getTheme(isDark);
  const colors = getColors(isDark);

  const formatted = data.map(d => ({
    ...d,
    label: ROLE_LABELS[d.name] ?? d.name,
  }));

  return (
    <div className="w-full h-full">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart
          data={formatted}
          margin={{ top: 20, right: 24, bottom: 10, left: 8 }}
        >
          <CartesianGrid strokeDasharray="4 4" stroke={theme.grid} vertical={false} />
          <XAxis
            dataKey="label"
            tick={{ fill: theme.text, fontSize: 13 }}
            axisLine={{ stroke: theme.axis }}
            tickLine={false}
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
          <Bar
            dataKey="value"
            radius={[6, 6, 0, 0]}
            maxBarSize={90}
            isAnimationActive={CHART_ANIMATION}
            animationDuration={CHART_ANIMATION_DURATION}
            animationEasing="ease-out"
          >
            {formatted.map((_, i) => (
              <Cell key={i} fill={colors[i % colors.length]} />
            ))}
            <LabelList
              dataKey="value"
              position="top"
              formatter={v => v.toLocaleString('es-CO')}
              style={{ fill: theme.muted, fontSize: 12, fontWeight: 600 }}
            />
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}
