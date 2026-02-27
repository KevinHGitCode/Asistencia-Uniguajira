import React, { useState } from 'react';
import {
  PieChart, Pie, Cell, Tooltip, Label, ResponsiveContainer,
} from 'recharts';
import {
  getColors, getTheme, getTooltipStyle, truncate,
} from './utils.js';
import {
  CHART_DENSITY, CHART_ANIMATION, CHART_ANIMATION_DURATION,
  PIE_INNER_RADIUS, PIE_OUTER_RADIUS, LABEL_MAX_CHARS,
} from '../config.js';

const RADIAN           = Math.PI / 180;
const LEGEND_PAGE_SIZE = 10; // items per page in the right legend

// ── "Otros" grouping ──────────────────────────────────────────────────────────

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

// ── Outer percentage label ────────────────────────────────────────────────────
// Only rendered for large slices AND only on the left half of the pie
// (right half would overlap the vertical legend).

function OuterLabel(props) {
  const { cx, cy, midAngle, outerRadius, percent, payload, theme } = props;
  if (percent < CHART_DENSITY.pieMinPercent / 100) return null;

  const sin    = Math.sin(-midAngle * RADIAN);
  const cos    = Math.cos(-midAngle * RADIAN);
  const isLeft = cos < 0;

  // Two-segment line: radial leg → horizontal elbow
  const x1 = cx + (outerRadius + 4)  * cos; // start at slice edge
  const y1 = cy + (outerRadius + 4)  * sin;
  const x2 = cx + (outerRadius + 27) * cos; // end of radial leg
  const y2 = cy + (outerRadius + 27) * sin;
  const x3 = x2 + (isLeft ? -12 : 12);        // elbow tip

  const textX  = isLeft ? x3 - 3 : x3 + 3;
  const anchor = isLeft ? 'end' : 'start';
  const name   = truncate(payload?.name ?? '', 16);
  const pct    = `${(percent * 100).toFixed(1)}%`;

  return (
    <g>
      <polyline
        points={`${x1},${y1} ${x2},${y2} ${x3},${y2}`}
        fill="none"
        stroke={theme.axis}
        strokeWidth={1}
        strokeLinejoin="round"
      />
      <text x={textX} y={y2} textAnchor={anchor} fontSize={11} fill={theme.muted}>
        <tspan x={textX} dy="-7">{name}</tspan>
        <tspan x={textX} dy="14" fontWeight={600} fill={theme.text}>{pct}</tspan>
      </text>
    </g>
  );
}

// ── Center label (total in the donut hole) ────────────────────────────────────

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
        {total.toLocaleString('es-CO')}
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
  const t    = getTheme(isDark);
  const item = payload[0];
  return (
    <div style={getTooltipStyle(isDark)}>
      <p style={{ color: t.tooltipText, fontWeight: 600, marginBottom: 4 }}>
        {item.name}
      </p>
      <p style={{ color: item.payload.fill }}>
        Participantes: {item.value.toLocaleString('es-CO')}
      </p>
      <p style={{ color: t.muted, fontSize: 12 }}>
        Porcentaje: {total > 0 ? ((item.value / total) * 100).toFixed(1) : 0}%
      </p>
    </div>
  );
}

// ── Vertical legend with pagination ──────────────────────────────────────────

function VerticalLegend({ chartData, colors, isDark }) {
  const [page, setPage] = useState(0);
  const theme = getTheme(isDark);
  const pages = Math.ceil(chartData.length / LEGEND_PAGE_SIZE);
  const start = page * LEGEND_PAGE_SIZE;
  const items = chartData.slice(start, start + LEGEND_PAGE_SIZE);

  return (
    <div className="flex flex-col h-full justify-center gap-1 pl-2 pr-1">
      {/* Legend items */}
      <div className="flex flex-col gap-1.5 flex-1 justify-center min-h-0">
        {items.map((item, i) => {
          const color = colors[(start + i) % colors.length];
          return (
            <div key={item.name} className="flex items-start gap-2 min-w-0">
              <div
                className="shrink-0 rounded-full mt-[3px]"
                style={{ width: 8, height: 8, backgroundColor: color }}
              />
              <span
                className="text-[11px] leading-tight min-w-0 break-words"
                style={{ color: theme.text }}
                title={item.name}
              >
                {truncate(item.name, LABEL_MAX_CHARS.legend)}
              </span>
            </div>
          );
        })}
      </div>

      {/* Pagination controls */}
      {pages > 1 && (
        <div className="flex items-center justify-center gap-2 pt-2 shrink-0">
          <button
            type="button"
            onClick={() => setPage(p => Math.max(0, p - 1))}
            disabled={page === 0}
            className="w-5 h-5 flex items-center justify-center rounded text-[10px] transition-opacity disabled:opacity-25 hover:opacity-70"
            style={{ color: theme.muted }}
          >
            ▲
          </button>
          <span className="text-[11px] tabular-nums" style={{ color: theme.muted }}>
            {page + 1}/{pages}
          </span>
          <button
            type="button"
            onClick={() => setPage(p => Math.min(pages - 1, p + 1))}
            disabled={page === pages - 1}
            className="w-5 h-5 flex items-center justify-center rounded text-[10px] transition-opacity disabled:opacity-25 hover:opacity-70"
            style={{ color: theme.muted }}
          >
            ▼
          </button>
        </div>
      )}
    </div>
  );
}

// ── Main component ────────────────────────────────────────────────────────────

/**
 * Pie / donut chart — Participantes por Programa.
 *
 * Layout:
 *  - Left (~62%): donut chart with center total + outer % labels on big left-side slices
 *  - Right (~38%): vertical paginated legend
 *
 * Features:
 *  - Slices below pieMinPercent% → grouped into "Otros (n)"
 *  - Inner radius > 0% → shows total participantes in the donut center
 */
export function ProgramParticipantsPie({ data, isDark }) {
  const theme     = getTheme(isDark);
  const colors    = getColors(isDark);
  const chartData = groupSmallSlices(data, CHART_DENSITY.pieMinPercent);
  const total     = chartData.reduce((s, d) => s + (d.value ?? 0), 0);
  const isDonut   = PIE_INNER_RADIUS !== '0%';

  return (
    <div className="w-full h-full flex items-center">
      {/* ── Pie / donut ── */}
      <div className="h-full min-w-0" style={{ flex: '0 0 62%' }}>
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
              labelLine={false}
              label={(props) => <OuterLabel {...props} theme={theme} />}
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
      </div>

      {/* ── Right legend ── */}
      <div className="h-full min-w-0" style={{ flex: '0 0 38%' }}>
        <VerticalLegend chartData={chartData} colors={colors} isDark={isDark} />
      </div>
    </div>
  );
}
