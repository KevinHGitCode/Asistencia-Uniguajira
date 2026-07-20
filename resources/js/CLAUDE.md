# resources/js — Frontend (islas React + JS vanilla)

## Patrón "React Islands"

La app es Livewire/Blade; React se monta solo en divs concretos de ciertas vistas.
Cada isla tiene su entry declarado en el `input` de `vite.config.js` y se carga con
`@vite(...)` desde la vista Blade.

| Isla | Mount point | Entry |
|---|---|---|
| Estadísticas (varias apps por subpágina) | `#statistics-react-root` | `statistics/index.jsx` |
| Participantes (admin) | `#participants-react-root` | `participants/index.jsx` |
| Gráficos del detalle de evento | ver `events/index.jsx` | `events/index.jsx` |
| Mapper de formatos PDF | — | `administration/formats/pdf-mapper.jsx` |

**`wire:navigate` (Livewire 3):** cada `index.jsx` debe re-montar la isla en el evento
`livewire:navigated`, no solo en `DOMContentLoaded` — si no, la isla desaparece al navegar
sin recarga completa.

## Convenciones de las islas

- Constantes ajustables por el usuario en `statistics/config.js` — no hardcodear valores nuevos
  en los componentes.
- **Tema claro/oscuro:** se detecta con `document.documentElement.classList.contains('dark')`
  más un MutationObserver sobre el `classList` (`statistics/hooks/useTheme.js`). La clase la
  controla Flux vía `@fluxAppearance` — no leer `prefers-color-scheme` directamente.
- **Fetch:** peticiones en paralelo con `AbortController` (`statistics/hooks/useStatistics.js`).
  Las APIs (`/api/statistics/*`, `/api/participants`) usan middleware `web + auth`, así que el
  fetch se autentica con la cookie de sesión (same-origin); no hay tokens.
- **Puente React ↔ Livewire** (listado de participantes, ADR-0008): la tabla vive en React;
  los modales de edición/eliminación en el componente Livewire `Admin\ParticipantsList`.
  React abre modales con `Livewire.dispatch('open-edit-participant' | 'open-delete-participant')`
  y Livewire emite `participants-refresh` para que React recargue.

## Legacy

- `charts.js`, `charts/` y `charts-details-event.js` usan ECharts vía CDN (legacy). Los gráficos
  nuevos van en React + Recharts dentro de una isla; no ampliar el código ECharts.
- Los scripts vanilla (`handle-sidebar.js`, `copy-link-events.js`, `scroll-activity.js`, etc.)
  se mantienen como están; no migrarlos a React sin que se pida.

## Peso del bundle

React + Recharts pesa ~585 KB minificado (~177 KB gzip). Aceptable para dashboards internos,
pero **no cargar islas React en las vistas públicas** (acceso por QR), que deben seguir ligeras.
