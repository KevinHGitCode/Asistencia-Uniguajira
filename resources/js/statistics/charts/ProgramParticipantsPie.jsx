import React from 'react';
import {
  PieChart, Pie, Cell, Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import {
  getColors, getTheme, getTooltipStyle, truncate,
} from './utils.js';
import {
  CHART_DENSITY, CHART_ANIMATION, CHART_ANIMATION_DURATION,
  PIE_INNER_RADIUS, PIE_OUTER_RADIUS, LABEL_MAX_CHARS,
} from '../config.js';

/** Etiqueta exterior del sector de la torta. */
function renderLabel({ cx, cy, midAngle, outerRadius, percent, index, data, isDark, theme }) {
  if (percent < CHART_DENSITY.pieMinPercent / 100) return null;

  const RADIAN  = Math.PI / 180;
  const radius  = outerRadius * 1.15;
  const x = cx + radius * Math.cos(-midAngle * RADIAN);
  const y = cy + radius * Math.sin(-midAngle * RADIAN);

  return (
    <text
      x={x} y={y}
      textAnchor={x > cx ? 'start' : 'end'}
      dominantBaseline="central"
      fontSize={11}
      fill={theme.muted}
    >
      {`${(percent * 100).toFixed(1)}%`}
    </text>
  );
}

/** Tooltip personalizado. */
function CustomTooltip({ active, payload, total, isDark }) {
  if (!active || !payload?.length) return null;
  const t    = getTheme(isDark);
  const item = payload[0];

  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4 }}>
        {item.name}
      </p>
      <p style={{ color: item.payload.fill }}>
        {item.value.toLocaleString('es-CO')} participantes
      </p>
      <p style={{ color: t.muted, fontSize: 12 }}>
        {total > 0 ? ((item.value / total) * 100).toFixed(1) : 0}% del total
      </p>
    </div>
  );
}

/** Formateador de la leyenda: trunca y colorea. */
function LegendFormatter(value, entry, isDark) {
  const t = getTheme(isDark);
  return (
    <span style={{ color: t.text, fontSize: 12 }}>
      {truncate(value, LABEL_MAX_CHARS.legend)}
    </span>
  );
}

/**
 * Gráfico de torta/dona: Participantes por Programa.
 * - Etiquetas externas se ocultan cuando hay muchos segmentos.
 * - Segmentos < pieMinPercent% no muestran etiqueta.
 */
export function ProgramParticipantsPie({ data, isDark }) {
  const theme    = getTheme(isDark);
  const colors   = getColors(isDark);
  const total    = data.reduce((s, d) => s + d.value, 0);
  const showOuter = data.length <= CHART_DENSITY.pieHideLabelAt;

  return (
    <div className="w-full h-full">
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={data}
            dataKey="value"
            nameKey="name"
            cx="50%"
            cy="47%"
            innerRadius={PIE_INNER_RADIUS}
            outerRadius={PIE_OUTER_RADIUS}
            paddingAngle={data.length > 1 ? 1.5 : 0}
            labelLine={showOuter}
            label={showOuter
              ? (props) => renderLabel({ ...props, data, isDark, theme })
              : false
            }
            isAnimationActive={CHART_ANIMATION}
            animationDuration={CHART_ANIMATION_DURATION}
            animationEasing="ease-out"
          >
            {data.map((_, i) => (
              <Cell
                key={i}
                fill={colors[i % colors.length]}
                stroke="none"
              />
            ))}
          </Pie>

          <Tooltip
            content={<CustomTooltip total={total} isDark={isDark} />}
          />

          <Legend
            layout="horizontal"
            verticalAlign="bottom"
            align="center"
            iconType="circle"
            iconSize={8}
            formatter={(value) => LegendFormatter(value, null, isDark)}
            wrapperStyle={{ paddingTop: 12, fontSize: 12 }}
          />
        </PieChart>
      </ResponsiveContainer>
    </div>
  );
}
