import { CHART_COLORS, THEME_TOKENS, LABEL_MAX_CHARS } from '../config.js';

/** Devuelve la paleta de colores según el tema. */
export function getColors(isDark) {
  return CHART_COLORS.map(c => (isDark ? c.dark : c.light));
}

/** Color individual por índice (hace wrap si hay más ítems que colores). */
export function getColor(index, isDark) {
  const colors = getColors(isDark);
  return colors[index % colors.length];
}

/** Tokens de tema (texto, grid, ejes, tooltips). */
export function getTheme(isDark) {
  return isDark ? THEME_TOKENS.dark : THEME_TOKENS.light;
}

/** Recorta una cadena y añade "…" si supera el largo máximo. */
export function truncate(str, maxLen = LABEL_MAX_CHARS.xAxis) {
  if (!str) return '';
  if (str.length <= maxLen) return str;
  return str.slice(0, maxLen - 1) + '…';
}

/**
 * Calcula el ángulo óptimo para las etiquetas del eje X
 * según el número de ítems y el ancho disponible del contenedor.
 *
 * @param {number} itemCount      - número de ítems en el eje
 * @param {number} containerWidth - ancho del contenedor en píxeles
 * @returns {{ angle: number, textAnchor: string, tickHeight: number }}
 */
export function getLabelAngle(itemCount, containerWidth) {
  if (itemCount === 0) return { angle: 0, textAnchor: 'middle', tickHeight: 24 };

  const avgPx = containerWidth / itemCount;

  if (avgPx > 90)  return { angle:   0, textAnchor: 'middle', tickHeight: 24 };
  if (avgPx > 55)  return { angle: -30, textAnchor: 'end',    tickHeight: 48 };
  return               { angle: -45, textAnchor: 'end',    tickHeight: 60 };
}

/** Estilo inline para el contenedor del Tooltip de Recharts. */
export function getTooltipStyle(isDark) {
  const t = getTheme(isDark);
  return {
    backgroundColor: t.background,
    border:          `1px solid ${t.border}`,
    borderRadius:    '10px',
    color:           t.tooltipText,
    fontSize:        '13px',
    boxShadow:       '0 4px 12px rgba(0,0,0,0.12)',
    padding:         '10px 14px',
    lineHeight:      '1.5',
  };
}

/** Props comunes para los ejes de Recharts. */
export function getAxisTickStyle(isDark, small = false) {
  const t = getTheme(isDark);
  return { fill: t.muted, fontSize: small ? 10 : 12 };
}
