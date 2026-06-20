---
tipo: adr
descripcion: ADR-0006 (propuesta) — Migrar los formularios de flyout lateral a modal centrado
actualizado: 2026-06-20
---

# ADR-0006 · Formularios en modal centrado (no flyout lateral)

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `resources/views/livewire/user/create-user-modal.blade.php` y
  `edit-user-modal.blade.php` usan `<flux:modal variant="flyout">` (panel lateral). Otros modales
  (eventos, administración) ya son centrados.

## Contexto
Los formularios de **usuario** aparecen como *flyout* pegado a un lateral. En pantallas anchas
desaprovechan el espacio (columna estrecha, mucho scroll vertical) e introducen inconsistencia
visual con el resto de modales del sistema, que ya son centrados.

## Decisión propuesta
Cambiar esos formularios a **modal centrado** (quitar `variant="flyout"` → variante por defecto /
centrada de Flux), con un ancho cómodo (p.ej. `max-w-lg`/`max-w-2xl`) que permita **2 columnas**
en los campos donde tenga sentido, aprovechando mejor el ancho.

## Consecuencias
- ➕ Mejor uso del espacio en escritorio y **consistencia** con los demás modales.
- ➕ Menos scroll en formularios con varios campos.
- ➖ Revisar responsive (en móvil el modal centrado debe seguir siendo cómodo y full-width).
- 🔁 Cambio sólo de presentación; la lógica Livewire no cambia. Coordina con los selectores ya
  migrados (chips de dependencias) para que se vean bien en el nuevo ancho.

## Alternativas consideradas
- **Mantener flyout** pero más ancho: sigue siendo inconsistente con el resto.
- **Rediseño completo** de los formularios: fuera de alcance; esto es solo el contenedor.

## Pendiente para aceptar
- [ ] Inventario de todos los `variant="flyout"` a migrar.
- [ ] Definir anchos y grilla de campos por formulario.
- [ ] Rama sugerida: `refactor/formularios-modal-centrado` (🟢 solo UI).

## Relacionado
[[convenciones]] · [[mapa-de-modulos]]
