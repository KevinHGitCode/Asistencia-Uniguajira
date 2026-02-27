/**
 * Utility functions for event charts
 */

// ── Theme & Colors ──────────────────────────────────────────────────────────

export function getTheme(isDark) {
  return {
    bg: isDark ? '#09090b' : '#ffffff',
    text: isDark ? '#fafafa' : '#09090b',
    muted: isDark ? '#a1a1a1' : '#71717a',
    axis: isDark ? '#3f3f46' : '#e4e4e7',
    tooltip: isDark ? 'rgba(20, 20, 20, 0.95)' : 'rgba(250, 250, 250, 0.95)',
    tooltipText: isDark ? '#fafafa' : '#09090b',
  };
}

export function getColors(isDark) {
  // Recharts color palette
  return [
    '#3b82f6', // blue
    '#10b981', // emerald
    '#f59e0b', // amber
    '#ef4444', // red
    '#8b5cf6', // violet
    '#ec4899', // pink
    '#14b8a6', // teal
    '#f97316', // orange
    '#06b6d4', // cyan
    '#6366f1', // indigo
  ];
}

export function getTooltipStyle(isDark) {
  const theme = getTheme(isDark);
  return {
    backgroundColor: theme.tooltip,
    border: `1px solid ${isDark ? '#3f3f46' : '#e4e4e7'}`,
    borderRadius: '8px',
    padding: '8px 12px',
    color: theme.tooltipText,
  };
}

// ── Label Utilities ────────────────────────────────────────────────────────

/**
 * Truncate text to maxChars with ellipsis
 */
export function truncate(text, maxChars) {
  if (!text) return '';
  return text.length > maxChars ? text.substring(0, maxChars - 1) + '…' : text;
}

/**
 * Format number in es-CO locale (with dots/commas)
 */
export function formatNumber(num) {
  return num.toLocaleString('es-CO');
}
