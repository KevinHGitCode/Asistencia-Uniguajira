import React, { useState } from 'react';
import {
  PieChart, Pie, Cell, Tooltip, Label, ResponsiveContainer,
} from 'recharts';
import {
  getColors, getTheme, getTooltipStyle, truncate, formatNumber,
} from './utils.js';
import {
  CHART_DENSITY, CHART_ANIMATION, CHART_ANIMATION_DURATION,
  PIE_INNER_RADIUS, PIE_OUTER_RADIUS, LABEL_MAX_CHARS,
} from './config.js';



// ── Group small slices ────────────────────────────────────────────────────────

function groupSmallSlices(raw, minPercent) {
  const total = raw.reduce((s, d) => s + (d.value ?? 0), 0);
  if (total === 0 || minPercent <= 0) return raw;

  const threshold = total * (minPercent / 100);
  const main = raw.filter(d => (d.value ?? 0) >= threshold);
  const small = raw.filter(d => (d.value ?? 0) < threshold);

  if (small.length <= 1) return raw;

  const othersVal = small.reduce((s, d) => s + (d.value ?? 0), 0);
  return [...main, { name: `Otros (${small.length})`, value: othersVal }];
}

// ── Outer percentage label ────────────────────────────────────────────────────
// Removed: No longer needed for compact pie charts

// ── Center label (total) ──────────────────────────────────────────────────────

function CenterLabel({ viewBox, total, theme }) {
  const { cx, cy } = viewBox;
  return (
    <>
      <text
        x={cx} y={cy - 8}
        textAnchor="middle"
        dominantBaseline="central"
        fontSize={21}
        fontWeight={700}
        fill={theme.text}
      >
        {formatNumber(total)}
      </text>
      <text
        x={cx} y={cy + 13}
        textAnchor="middle"
        dominantBaseline="central"
        fontSize={11}
        fill={theme.muted}
      >
        participantes
      </text>
    </>
  );
}

// ── Custom tooltip ────────────────────────────────────────────────────────────

function CustomTooltip({ active, payload, total, isDark }) {
  if (!active || !payload?.length) return null;
  const t = getTheme(isDark);
  const item = payload[0];
  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4 }}>
        {item.name}
      </p>
      <p style={{ color: item.payload.fill }}>
        Participantes: {formatNumber(item.value)}
      </p>
      <p style={{ color: t.muted, fontSize: 12 }}>
        Porcentaje: {total > 0 ? ((item.value / total) * 100).toFixed(1) : 0}%
      </p>
    </div>
  );
}



// ── Main component ────────────────────────────────────────────────────────────

export function ProgramParticipantsPie({ data, isDark }) {
  const theme = getTheme(isDark);
  const colors = getColors(isDark);
  const chartData = groupSmallSlices(data, CHART_DENSITY.pieMinPercent);
  const total = chartData.reduce((s, d) => s + (d.value ?? 0), 0);
  const isDonut = PIE_INNER_RADIUS !== '0%';

  return (
    <div className="relative w-full h-full">
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={chartData}
            dataKey="value"
            nameKey="name"
            cx="50%"
            cy="50%"
            innerRadius={PIE_INNER_RADIUS}
            outerRadius={PIE_OUTER_RADIUS}
            paddingAngle={chartData.length > 1 ? 2 : 0}
            isAnimationActive={CHART_ANIMATION}
            animationDuration={CHART_ANIMATION_DURATION}
            animationEasing="ease-out"
          >
            {chartData.map((_, i) => (
              <Cell
                key={i}
                fill={colors[i % colors.length]}
                stroke={isDark ? '#18181b' : '#ffffff'}
                strokeWidth={2}
              />
            ))}

            {isDonut && (
              <Label
                content={(props) => (
                  <CenterLabel {...props} total={total} theme={theme} />
                )}
              />
            )}
          </Pie>

          <Tooltip content={<CustomTooltip total={total} isDark={isDark} />} />
        </PieChart>
      </ResponsiveContainer>

      <ResponsiveLegend chartData={chartData} colors={colors} isDark={isDark} />
    </div>
  );
}

// ── Responsive legend component ───────────────────────────────────────────────

function ResponsiveLegend({ chartData, colors, isDark }) {
  const theme = getTheme(isDark);
  const [page, setPage] = useState(0);
  const itemsPerPage = 4;
  const pages = Math.ceil(chartData.length / itemsPerPage);
  const start = page * itemsPerPage;
  const items = chartData.slice(start, start + itemsPerPage);

  return (
    <div className="absolute bottom-2 left-2 right-2 flex items-center justify-center gap-1">
      <div className="flex gap-2 flex-wrap justify-center">
        {items.map((item, i) => {
          const color = colors[(start + i) % colors.length];
          return (
            <div key={item.name} className="flex items-center gap-1 text-xs">
              <div
                className="rounded-full"
                style={{ width: 6, height: 6, backgroundColor: color }}
              />
              <span style={{ color: theme.text }}>
                {truncate(item.name, 12)}
              </span>
            </div>
          );
        })}
      </div>

      {pages > 1 && (
        <div className="flex items-center gap-1 ml-2">
          <button
            type="button"
            onClick={() => setPage(p => Math.max(0, p - 1))}
            disabled={page === 0}
            className="text-[8px] transition-opacity disabled:opacity-25 hover:opacity-70"
            style={{ color: theme.muted }}
          >
            ◀
          </button>
          <span className="text-[8px]" style={{ color: theme.muted }}>
            {page + 1}/{pages}
          </span>
          <button
            type="button"
            onClick={() => setPage(p => Math.min(pages - 1, p + 1))}
            disabled={page === pages - 1}
            className="text-[8px] transition-opacity disabled:opacity-25 hover:opacity-70"
            style={{ color: theme.muted }}
          >
            ▶
          </button>
        </div>
      )}
    </div>
  );
}
