import React from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
} from 'recharts';
import { getColors, getTheme, getTooltipStyle, truncate, formatNumber } from './utils.js';
import { CHART_ANIMATION, CHART_ANIMATION_DURATION } from './config.js';

function CustomTooltip({ active, payload, isDark }) {
  if (!active || !payload?.length) return null;
  const theme = getTheme(isDark);
  const item = payload[0];
  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: theme.tooltipText, fontWeight: 600, marginBottom: 4 }}>
        {item.payload.name}
      </p>
      <p style={{ color: item.fill }}>
        Participantes: {formatNumber(item.value)}
      </p>
    </div>
  );
}

export function RoleParticipantsBar({ data, isDark }) {
  const theme = getTheme(isDark);
  const colors = getColors(isDark);
  const barColor = colors[1];

  // Sort by value descending
  const chartData = [...data].sort((a, b) => (b.value ?? 0) - (a.value ?? 0));

  return (
    <ResponsiveContainer width="100%" height="100%">
      <BarChart
        data={chartData}
        margin={{ top: 20, right: 30, left: 30, bottom: 20 }}
      >
        <CartesianGrid strokeDasharray="3 3" stroke={theme.axis} />
        <YAxis stroke={theme.muted} tick={{ fontSize: 10, fill: theme.text }} />
        <Tooltip content={<CustomTooltip isDark={isDark} />} />
        <Bar
          dataKey="value"
          fill={barColor}
          isAnimationActive={CHART_ANIMATION}
          animationDuration={CHART_ANIMATION_DURATION}
          radius={[8, 8, 0, 0]}
        />
      </BarChart>
    </ResponsiveContainer>
  );
}
