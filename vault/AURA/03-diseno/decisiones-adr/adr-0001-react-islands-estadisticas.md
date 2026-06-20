---
tipo: adr
descripcion: ADR-0001 — Usar React Islands + Recharts para el módulo de estadísticas
actualizado: 2026-06-20
---

# ADR-0001 · React Islands para estadísticas

- **Estado:** 🟢 Aceptada
- **Fecha:** 2026-06-20 (registrada; la decisión es anterior, evidenciada en el código)
- **Contexto del repo:** `resources/js/statistics/`, `package.json` (React 19 + Recharts +
  `@vitejs/plugin-react`).

## Contexto
El módulo de estadísticas necesita gráficos ricos e interactivos (barras, torta, top,
comparador de eventos) con buen rendimiento. El resto de la app es Blade + Livewire + Alpine.
Existió una primera versión con ECharts vía CDN (queda como legacy).

## Decisión
Implementar las estadísticas como **islas de React** montadas dentro de vistas Blade
(`#statistics-react-root`), usando **Recharts**, con soporte de `wire:navigate` (re-montaje en
`livewire:navigated`). El estado de filtros vive en React; los datos vienen de
`/api/statistics/*`.

## Consecuencias
- ➕ Gráficos interactivos y mantenibles; ecosistema React maduro.
- ➕ Aísla la complejidad del dashboard sin reescribir toda la app.
- ➖ Añade React + Recharts al bundle (~585 KB min / ~177 KB gzip — aceptable para dashboard).
- ➖ Conviven 3 estilos de UI (Blade/Alpine/React) → ver [[arquitectura]].
- 🔁 Requiere `@vitejs/plugin-react` en Vite y manejar el ciclo de vida con Livewire 3.

## Alternativas consideradas
- Seguir con **ECharts vía CDN** (legacy): menos integrado, sin tipado/bundling.
- Gráficos con **Alpine + librería JS**: menos potente para la interactividad requerida.

## Relacionado
[[arquitectura]] · [[mapa-de-modulos]]
