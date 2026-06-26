---
tipo: adr
descripcion: ADR-0023 (propuesta) — Pestaña para alternar entre gráfica circular y de barras conservando funciones
actualizado: 2026-06-25
---

# ADR-0023 · Alternar tipo de gráfica (circular / barras)

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `resources/js/statistics/charts/` (`ProgramParticipantsPie.jsx`,
  `ProgramAttendancesBar.jsx`, `TopHorizontalBar.jsx`, etc.), `components/ChartCard.jsx`,
  `ChartToolbar.jsx`. Estadísticas en React ([[adr-0001-react-islands-estadisticas]]).

## Contexto
Algunas visualizaciones se muestran como **gráfica circular** (torta/dona). A varios usuarios les
resultan más legibles las **barras** (sobre todo con muchas categorías o para comparar magnitudes).
Hoy el tipo de gráfica es fijo por tarjeta.

## Decisión
Añadir, en el encabezado de la tarjeta (junto a la `ChartToolbar`), un **conmutador** (pestañas o
toggle) **circular ↔ barras**. Al cambiar, la misma serie de datos se **re-renderiza** en el otro
tipo, **conservando** todas las funciones existentes (copiar imagen, ver datos, copiar tabla, CSV/
Excel, tema claro/oscuro). La preferencia puede recordarse por tarjeta en `localStorage`.

## Consecuencias
- ➕ Cada usuario elige la representación que mejor entiende, **sin perder** funciones.
- ➕ Reutiliza los componentes de barras que ya existen.
- ➖ Cada tarjeta debe soportar dos render y un estado de "tipo activo".
- 🔁 Definir qué tarjetas tienen sentido en ambos formatos (algunas solo barras u solo torta).

## Alternativas consideradas
- **Tipo fijo (actual)** — simple, pero no acomoda preferencias.
- **Dejar elegir solo a un admin global** — menos flexible que por-usuario/por-tarjeta.

## Relacionado
[[adr-0001-react-islands-estadisticas]] · [[hu-0005-copiar-graficas-y-tabla-a-word]] ·
[[adr-0024-mejorar-imagen-copiada-de-graficas]]
