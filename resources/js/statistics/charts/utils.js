import { CHART_COLORS, THEME_TOKENS, LABEL_MAX_CHARS, PIE_TOP_N, BAR_TOP_N } from '../config.js';

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

/**
 * Agrupa los datos por posición: muestra los top N ítems y agrupa el resto
 * en una categoría "Otros (n)". Los datos se ordenan por valor descendente.
 *
 * @param {Array} data    - [{ name, value, ... }]
 * @param {number} maxItems - máximo de ítems visibles (default PIE_TOP_N)
 * @returns {Array}
 */
export function groupTopN(data, maxItems = PIE_TOP_N) {
  if (!data || data.length <= maxItems) return data;

  const sorted = [...data].sort((a, b) => (b.value ?? 0) - (a.value ?? 0));
  const top    = sorted.slice(0, maxItems);
  const rest   = sorted.slice(maxItems);

  if (rest.length === 0) return top;

  const othersVal = rest.reduce((sum, d) => sum + (d.value ?? 0), 0);
  if (othersVal === 0) return top;

  return [...top, { name: `Otros (${rest.length})`, value: othersVal, isOthers: true }];
}
