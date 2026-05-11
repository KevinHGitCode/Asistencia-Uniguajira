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
  barTinyAt:      12,   // Ítems en barra → reducir tamaño de fuente a mínimo
  pieHideLabelAt: 9,    // Ítems en torta → ocultar etiquetas externas (solo %)
  pieMinPercent:  5,    // % mínimo para mostrar etiqueta en la torta
  horizontalMax:  10,   // Máximo de filas visibles en barras horizontales
};

// ---------------------------------------------------------------------------
// TOP-N GROUPING — Agrupación por posición (no por porcentaje)
// Los ítems que queden fuera del top se agrupan en "Otros (n)"
// ---------------------------------------------------------------------------
export const PIE_TOP_N = 7;   // Máximo de slices visibles en tortas
export const BAR_TOP_N = 12;  // Máximo de barras visibles en horizontales

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
export const PIE_INNER_RADIUS = '42%';  // '0%' = torta sólida | '>0%' = dona
export const PIE_OUTER_RADIUS = '68%';

// ---------------------------------------------------------------------------
// ANIMACIONES
// CHART_ANIMATION: true = con animación | false = sin animación (más rápido)
// CHART_ANIMATION_DURATION: milisegundos que dura la animación de entrada
// ---------------------------------------------------------------------------
export const CHART_ANIMATION          = true;
export const CHART_ANIMATION_DURATION = 700;

// ---------------------------------------------------------------------------
// PALETA DE COLORES (índice = orden de aparición)
// Los primeros 3 usan los colores institucionales de la Universidad de La Guajira.
// ---------------------------------------------------------------------------
export const CHART_COLORS = [
  { light: '#62a9b6', dark: '#7ec0cb' }, // azul-teal institucional (1°)
  { light: '#e2a542', dark: '#f0bc6a' }, // ámbar institucional    (2°)
  { light: '#cc5e50', dark: '#e07a6d' }, // terracota institucional (3°)
  { light: '#8b5cf6', dark: '#a78bfa' }, // violeta
  { light: '#10b981', dark: '#34d399' }, // esmeralda
  { light: '#3b82f6', dark: '#60a5fa' }, // azul
  { light: '#ec4899', dark: '#f472b6' }, // rosa
  { light: '#84cc16', dark: '#a3e635' }, // lima
  { light: '#f97316', dark: '#fb923c' }, // naranja
  { light: '#14b8a6', dark: '#2dd4bf' }, // teal
];

// ---------------------------------------------------------------------------
// COLOR PRINCIPAL DE LAS BARRAS ÚNICAS  (cuando el gráfico usa un solo color)
// ---------------------------------------------------------------------------
export const BAR_PRIMARY_COLOR = {
  light: '#62a9b6', // azul-teal institucional
  dark:  '#7ec0cb', // azul-teal institucional (claro)
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
