---
tipo: adr
descripcion: ADR-0024 (propuesta) — La imagen copiada de una gráfica debe recortarse y espaciarse de forma aceptable
actualizado: 2026-06-25
---

# ADR-0024 · Mejorar la imagen copiada de las gráficas

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `resources/js/statistics/components/ChartToolbar.jsx` → `captureAsImage()`
  (copia la gráfica como PNG usando `containerEl.clientWidth/Height` + `PADDING = 20` y dibuja la
  leyenda HTML por su posición en pantalla).

## Contexto
`captureAsImage` reproduce **tal cual** lo que está en pantalla: usa el tamaño del contenedor y la
**posición real** de la leyenda. Fuera de la app eso se ve mal:
- **Espacio en blanco grande** alrededor (el contenedor suele ser más alto que la gráfica).
- Si la gráfica está **pequeña** o mal distribuida en la tarjeta, **se copia pequeña**.
- Las **leyendas/viñetas** (color → programa) quedan **muy separadas** de la gráfica, igual que en la
  vista, y al sacarlas de contexto el espacio se ve "feo".

## Decisión
Que la imagen copiada se **componga para exportar**, no que clone la pantalla:
- **Recortar** al contenido real (gráfica + leyenda), eliminando el blanco sobrante.
- **Escalar** a un tamaño mínimo legible aunque en pantalla esté pequeña.
- **Reposicionar** la leyenda a una **distancia fija y compacta** respecto a la gráfica (no a su
  posición en el layout).
- Mantener título, tema (claro/oscuro) y alta resolución (DPR).

## Consecuencias
- ➕ Imágenes **listas para pegar** en informes/diapositivas, prolijas y de tamaño consistente.
- ➕ Refuerza [[hu-0005-copiar-graficas-y-tabla-a-word]].
- ➖ La captura deja de ser "lo que ves es lo que copias": hay un layout de exportación aparte.
- 🔁 Ajustar el cálculo de `bounding box` del contenido y el dibujado de la leyenda en el `<canvas>`.

## Alternativas consideradas
- **Dejarlo como está** — fiel a pantalla pero feo al exportar.
- **Renderizar a un tamaño fijo grande** sin recorte — reduce blanco pero no resuelve leyendas
  separadas ni gráficas pequeñas.

## Relacionado
[[hu-0005-copiar-graficas-y-tabla-a-word]] · [[adr-0023-alternar-tipo-de-grafica-circular-barras]] ·
[[adr-0001-react-islands-estadisticas]]
