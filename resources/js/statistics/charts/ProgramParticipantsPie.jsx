import React from 'react';
import {
  PieChart, Pie, Cell, Tooltip, Legend, Label, ResponsiveContainer,
} from 'recharts';
import {
  getColors, getTheme, getTooltipStyle, truncate,
} from './utils.js';
import {
  CHART_DENSITY, CHART_ANIMATION, CHART_ANIMATION_DURATION,
  PIE_INNER_RADIUS, PIE_OUTER_RADIUS, LABEL_MAX_CHARS,
} from '../config.js';

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Group slices whose share is below `minPercent` into a single "Otros" entry.
 * If there is only 1 small slice, it's kept as-is (no point in grouping one item).
 */
function groupSmallSlices(raw, minPercent) {
  const total = raw.reduce((s, d) => s + (d.value ?? 0), 0);
  if (total === 0 || minPercent <= 0) return raw;

  const threshold = total * (minPercent / 100);
  const main      = raw.filter(d => (d.value ?? 0) >= threshold);
  const small     = raw.filter(d => (d.value ?? 0) <  threshold);

  if (small.length <= 1) return raw;

  const othersVal = small.reduce((s, d) => s + (d.value ?? 0), 0);
  return [...main, { name: `Otros (${small.length})`, value: othersVal }];
}

// ── Custom tooltip ────────────────────────────────────────────────────────────

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

// ── Legend formatter ──────────────────────────────────────────────────────────

function LegendFormatter(value, _entry, isDark) {
  const t = getTheme(isDark);
  return (
    <span style={{ color: t.text, fontSize: 12 }}>
      {truncate(value, LABEL_MAX_CHARS.legend)}
    </span>
  );
}

// ── Outer label (shown only when few slices) ──────────────────────────────────

function renderLabel({ cx, cy, midAngle, outerRadius, percent, theme }) {
  if (percent < CHART_DENSITY.pieMinPercent / 100) return null;

  const RADIAN = Math.PI / 180;
  const r = outerRadius * 1.17;
  const x = cx + r * Math.cos(-midAngle * RADIAN);
  const y = cy + r * Math.sin(-midAngle * RADIAN);

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

// ── Center label (donut only) ─────────────────────────────────────────────────

function CenterLabel({ viewBox, total, theme }) {
  const { cx, cy } = viewBox;
  return (
    <>
      <text
        x={cx} y={cy - 7}
        textAnchor="middle"
        dominantBaseline="central"
        fontSize={22}
        fontWeight={700}
        fill={theme.text}
      >
        {total.toLocaleString('es-CO')}
      </text>
      <text
        x={cx} y={cy + 14}
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

// ── Component ─────────────────────────────────────────────────────────────────

/**
 * Pie / donut chart — Participantes por Programa.
 *
 * Features:
 *  - Slices below `pieMinPercent` are grouped into "Otros (n)".
 *  - Outer % labels are hidden when there are more than `pieHideLabelAt` slices.
 *  - When inner radius > 0 (donut), shows the total in the centre.
 */
export function ProgramParticipantsPie({ data, isDark }) {
  const theme    = getTheme(isDark);
  const colors   = getColors(isDark);

  // Group small slices → "Otros"
  const chartData  = groupSmallSlices(data, CHART_DENSITY.pieMinPercent);
  const total      = chartData.reduce((s, d) => s + (d.value ?? 0), 0);
  const isDonut    = PIE_INNER_RADIUS !== '0%';
  const showOuter  = chartData.length <= CHART_DENSITY.pieHideLabelAt;

  return (
    <div className="w-full h-full">
      <ResponsiveContainer width="100%" height="100%">
        <PieChart>
          <Pie
            data={chartData}
            dataKey="value"
            nameKey="name"
            cx="50%"
            cy="47%"
            innerRadius={PIE_INNER_RADIUS}
            outerRadius={PIE_OUTER_RADIUS}
            paddingAngle={chartData.length > 1 ? 2 : 0}
            labelLine={showOuter && !isDonut}
            label={showOuter && !isDonut
              ? (props) => renderLabel({ ...props, theme })
              : false
            }
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

            {/* Center total — only for donut */}
            {isDonut && (
              <Label
                content={(props) => (
                  <CenterLabel {...props} total={total} theme={theme} />
                )}
              />
            )}
          </Pie>

          <Tooltip content={<CustomTooltip total={total} isDark={isDark} />} />

          <Legend
            layout="horizontal"
            verticalAlign="bottom"
            align="center"
            iconType="circle"
            iconSize={8}
            formatter={(value) => LegendFormatter(value, null, isDark)}
            wrapperStyle={{ paddingTop: 14, fontSize: 12 }}
          />
        </PieChart>
      </ResponsiveContainer>
    </div>
  );
}
