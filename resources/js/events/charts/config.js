// ── Chart Display Configuration ──

// Minimum percentage threshold: slices below this are grouped into "Otros"
export const CHART_DENSITY = {
  pieMinPercent: 3, // % threshold for grouping small slices
};

// Animation settings
export const CHART_ANIMATION = true;
export const CHART_ANIMATION_DURATION = 600; // ms

// Pie chart dimensions
export const PIE_INNER_RADIUS = '40%';  // donut hole size
export const PIE_OUTER_RADIUS = '85';   // outer ring size

// Bar chart dimensions
export const BAR_HEIGHT = 300; // height for bar charts

// Label truncation
export const LABEL_MAX_CHARS = {
  legend: 20,    // max chars in legend labels
  tooltip: 30,   // max chars in tooltip
};
