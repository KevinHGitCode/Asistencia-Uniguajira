---
tipo: adr
descripcion: ADR-0013 (propuesta) — Breadcrumbs del detalle de evento según el contexto de origen
actualizado: 2026-06-20
---

# ADR-0013 · Breadcrumbs del detalle de evento (contexto de origen)

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `resources/views/events/show.blade.php` (detalle de evento) tiene el
  breadcrumb **hardcodeado**: `['label' => 'Eventos', 'route' => 'events.list']` → 'Información'.

## Contexto
Al abrir el detalle de un evento, el breadcrumb siempre apunta a **"Tus eventos"** (`events.list`),
sin importar de dónde se llegó. Si entraste desde **"Todos los eventos"** (`admin.events.index`) o
desde los **eventos de un usuario** (detalle de usuario), el "atrás" del breadcrumb te lleva a un
listado **distinto al que estabas** — navegación incorrecta y confusa.

## Decisión propuesta
Hacer el breadcrumb del detalle **consciente del origen**:

- Pasar el contexto de origen al detalle (p. ej. `?from=todos` / `?from=usuario:{id}` /
  `?from=mis`), o resolverlo de forma segura, y construir el breadcrumb en consecuencia:
  - desde "Todos los eventos" → *Dashboard / Todos los eventos / Información*.
  - desde el detalle de un usuario → *Usuarios / {Usuario} / Información*.
  - desde "Tus eventos" (defecto) → *Eventos / Información*.
- Validar de paso que **todos** los breadcrumbs de eventos (y los eventos del detalle de usuario)
  apunten a donde corresponde.

## Consecuencias
- ➕ Navegación correcta: el "atrás" te devuelve a donde estabas.
- ➕ Arreglo pequeño y de alto impacto en percepción de calidad.
- ➖ Hay que propagar el origen en los enlaces a `events.show` desde cada listado; validar que no
  se pueda inyectar un breadcrumb inválido (sanitizar el `from`).
- 🔁 Tocar cada vista que enlaza a `events.show` (admin-events, detalle de usuario, "Tus eventos").

## Alternativas consideradas
- **Usar el referer HTTP**: frágil (puede faltar o falsearse); el parámetro explícito es más fiable.
- **Dejar el breadcrumb fijo**: mantiene el bug.

## Pendiente para aceptar
- [ ] Elegir mecanismo (parámetro `from` vs. otro) y sanitización.
- [ ] Inventario de enlaces a `events.show` a actualizar.
- [ ] Rama sugerida: `fix/breadcrumbs-detalle-evento` (🟢 UI/navegación).

## Relacionado
[[mapa-de-modulos]] · [[adr-0012-busqueda-y-filtros-en-eventos]]
