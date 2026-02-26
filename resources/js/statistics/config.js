/**
 * ============================================================
 *  CONFIGURACIÓN DE GRÁFICOS — Asistencia Uniguajira
 *
 *  Modifica estas constantes para ajustar el comportamiento
 *  y la apariencia de todos los gráficos de estadísticas.
 * ============================================================
 */

// ---------------------------------------------------------------------------
// ALTURA DE LOS GRÁFICOS (en píxeles)
// Aumenta o reduce para dar más/menos espacio vertical a cada tipo de gráfico.
// ---------------------------------------------------------------------------
export const CHART_HEIGHTS = {
  bar:           340,   // Barras verticales  (Asistencias por Programa)
  pie:           420,   // Torta / dona       (Participantes por Programa)
  horizontalBar: 290,   // Barras horizontales (Tops, Eventos por usuario)
  role:          260,   // Gráfico de roles   (pocos ítems, compacto)
};

// ---------------------------------------------------------------------------
// COLUMNAS DE LA GRILLA  ('full' = ancho completo | 'half' = media columna)
// En pantallas < md siempre ocupa el ancho completo.
// ---------------------------------------------------------------------------
export const CHART_GRID_COLS = {
  programAttendances:  'full',   // Asistencias por Programa
  programParticipants: 'full',   // Participantes por Programa (torta)
  topEvents:           'half',   // Top Eventos
  topParticipants:     'half',   // Top Participantes
  topUsers:            'half',   // Top Usuarios
  eventsByRole:        'half',   // Eventos por Rol
  eventsByUser:        'half',   // Eventos por Usuario
};

// ---------------------------------------------------------------------------
// DENSIDAD DE ETIQUETAS  (smart display)
// Controlan cuándo el gráfico activa el modo "compacto" para no saturarse.
// ---------------------------------------------------------------------------
export const CHART_DENSITY = {
  barRotateAt:    7,    // Ítems en barra → rotar etiquetas del eje X
  barTinyAt:      13,   // Ítems en barra → reducir tamaño de fuente a mínimo
  pieHideLabelAt: 9,    // Ítems en torta → ocultar etiquetas externas (solo %)
  pieMinPercent:  2.5,  // % mínimo para mostrar etiqueta en la torta
  horizontalMax:  10,   // Máximo de filas visibles en barras horizontales
};

// ---------------------------------------------------------------------------
// LONGITUD MÁXIMA DE NOMBRES ABREVIADOS EN EJES
// Si el nombre supera este largo, se recorta con "…"
// ---------------------------------------------------------------------------
export const LABEL_MAX_CHARS = {
  xAxis:      20,   // Eje X en barras verticales
  yAxisHoriz: 22,   // Eje Y en barras horizontales
  legend:     26,   // Leyenda de la torta
};

// ---------------------------------------------------------------------------
// RADIO DE LA TORTA / DONA
// PIE_INNER: '0%' = torta sólida | '>0%' = dona con hueco
// PIE_OUTER: radio exterior (100% = llena el contenedor)
// ---------------------------------------------------------------------------
export const PIE_INNER_RADIUS = '45%';  // '0%' = torta sólida | '>0%' = dona
export const PIE_OUTER_RADIUS = '68%';

// ---------------------------------------------------------------------------
// ANIMACIONES
// CHART_ANIMATION: true = con animación | false = sin animación (más rápido)
// CHART_ANIMATION_DURATION: milisegundos que dura la animación de entrada
// ---------------------------------------------------------------------------
export const CHART_ANIMATION          = true;
export const CHART_ANIMATION_DURATION = 550;

// ---------------------------------------------------------------------------
// PALETA DE COLORES (índice = orden de aparición)
// Cada color tiene variante para modo claro y modo oscuro.
// ---------------------------------------------------------------------------
export const CHART_COLORS = [
  { light: '#3b82f6', dark: '#60a5fa' }, // azul
  { light: '#10b981', dark: '#34d399' }, // esmeralda
  { light: '#8b5cf6', dark: '#a78bfa' }, // violeta
  { light: '#f59e0b', dark: '#fbbf24' }, // ámbar
  { light: '#ef4444', dark: '#f87171' }, // rojo
  { light: '#06b6d4', dark: '#22d3ee' }, // cian
  { light: '#ec4899', dark: '#f472b6' }, // rosa
  { light: '#84cc16', dark: '#a3e635' }, // lima
  { light: '#f97316', dark: '#fb923c' }, // naranja
  { light: '#14b8a6', dark: '#2dd4bf' }, // teal
];

// ---------------------------------------------------------------------------
// COLOR PRINCIPAL DE LAS BARRAS ÚNICAS  (cuando el gráfico usa un solo color)
// ---------------------------------------------------------------------------
export const BAR_PRIMARY_COLOR = {
  light: '#3b82f6', // blue-500
  dark:  '#60a5fa', // blue-400
};

// ---------------------------------------------------------------------------
// TOKENS DE TEMA  (colores de ejes, grid, texto, tooltips)
// No es necesario tocar esto salvo que quieras cambiar grises/fondos.
// ---------------------------------------------------------------------------
export const THEME_TOKENS = {
  light: {
    text:         '#374151', // gris oscuro para etiquetas
    muted:        '#6b7280', // gris medio para ejes secundarios
    grid:         '#f3f4f6', // líneas de cuadrícula
    axis:         '#e5e7eb', // bordes de eje
    background:   '#ffffff', // fondo del tooltip
    border:       '#e5e7eb', // borde del tooltip
    tooltipText:  '#111827', // texto principal del tooltip
  },
  dark: {
    text:         '#f9fafb',
    muted:        '#9ca3af',
    grid:         '#374151',
    axis:         '#4b5563',
    background:   '#1f2937',
    border:       '#374151',
    tooltipText:  '#f9fafb',
  },
};
