---
name: anticipar-necesidades
description: Úsala al diseñar, implementar o terminar una funcionalidad en Asistencia Uniguajira para anticipar necesidades contiguas que el usuario no pidió explícitamente — auditoría (quién/cuándo), métricas internas, historial/reversibilidad, estados vacío/carga/error, escala, búsqueda/filtros, permisos/seguridad, navegación/breadcrumbs y tiempo real — y proponerlas, registrando las no urgentes en el vault AURA.
---

# Anticipar necesidades del usuario

Antes de cerrar una funcionalidad en este proyecto, **detente y pásala por estas lentes**. La
meta no es hacer trabajo de más, sino **detectar 2–4 necesidades contiguas concretas** que el
usuario probablemente querrá, ofrecerlas y dejar registradas las que no son urgentes.

> Regla de oro: **propón, no impongas.** No implementes lo no pedido sin confirmar; sí déjalo
> visible (sugerencia al final del turno + nota en AURA).

## Lentes (recórrelas todas, rápido)

1. **Auditoría / trazabilidad** — ¿queda registro de *quién* hizo esto y *cuándo*?
   (`user_id`, timestamps, `ActivityLogService`). Ej.: en un lote de importación, quién lo subió.
2. **Métricas internas** — ¿conviene medir *duración* o *volumen* para el futuro? Guardar en BD
   aunque no se muestre en UI (columnas internas). Ej.: `import_batches.duration_ms`.
3. **Historial / reversibilidad** — ¿se puede *volver* a ver, descargar o deshacer? ¿Hay un
   listado de lo ya procesado? ¿Se puede aprobar/rechazar/revertir?
4. **Estados vacío / carga / error** — ¿se distingue "vacío" de "cargando"? ¿Hay spinner,
   anti-doble-submit, mensajes de error claros, conteos visibles a primera vista?
5. **Escala** — ¿qué pasa con 30k filas/registros? Paginación e índices, búsqueda **server-side**,
   lectura eficiente (ej.: fast-path CSV vs PhpSpreadsheet), no cargar todo en memoria/DOM.
6. **Búsqueda y filtros** — ¿el listado tiene buscador y filtros útiles (por estado, fecha,
   estamento, sede…)? Reutilizar `x-ui.searchable-select`.
7. **Permisos / seguridad** — ¿quién puede? ¿re-autenticación (contraseña) en acciones
   destructivas o masivas? ¿rate limit en rutas públicas? ¿se valida la sede?
8. **Navegación** — ¿los **breadcrumbs** reflejan de dónde vienes? ¿el "atrás" lleva a donde
   estabas y no a otro listado? ¿`wire:navigate` no rompe el contexto?
9. **Tiempo real / frescura** — ¿el dato necesita estar al día (contadores, usuarios activos,
   progreso de un job)? ¿poll, eventos o caché corta?
10. **Multi-sede** — ¿respeta `CampusScopeService` donde aplica? Revisa `[[migracion-multi-sede]]`
    antes de tocar users/events/dependencies/areas/programs.
11. **Consistencia de UI** — ¿usa los patrones existentes (selector con búsqueda, modales
    centrados, scrollbars sutiles, tablas con paginación) en vez de inventar otro estilo?

## Qué hacer con lo que encuentres

- **Pequeño y obvio** (un contador, un breadcrumb correcto, un índice) → ofrécelo en el turno.
- **Con consecuencias** (esquema, contrato, infra, UX grande) → crea un **ADR 🟡 propuesta** en
  `vault/AURA/03-diseno/decisiones-adr/` y enlázalo desde `Inicio.md` y `roadmap.md`.
- **Idea suelta sin madurar** → añádela a `vault/AURA/05-ideas/ideas.md`.
- Cierra el turno con una lista breve de **2–4 sugerencias** (no un muro de texto).

## Ejemplos reales en este repo (para calibrar)

- *Importación de participantes* disparó: "¿quién subió el lote?" (`user_id`), "¿cuánto tardó?"
  (`duration_ms`, solo BD), **historial** de lotes, y poder **descargar omitidos después**
  → ver [[adr-0004-pasarela-de-revision-para-importacion-de-participantes]].
- *Listados grandes* → búsqueda/orden server-side y React island
  → [[adr-0008-listado-participantes-en-react]].
- *Acciones masivas* → confirmación + contraseña del admin (re-autenticación).

> Mantén este criterio alineado con [[reglas-de-oro]] y las [[brechas-conocidas]].
