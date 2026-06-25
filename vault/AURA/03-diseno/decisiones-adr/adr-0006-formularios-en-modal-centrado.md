---
tipo: adr
descripcion: ADR-0006 (propuesta) — Migrar los formularios de flyout lateral a modal centrado
actualizado: 2026-06-20
---

# ADR-0006 · Formularios en modal centrado (no flyout lateral)

- **Estado:** 🟢 Implementado
- **Fecha:** 2026-06-20
- **Implementado:** 2026-06-21
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

## Implementación

- **Rama:** `refactor/formularios-modal-centrado`.
- **Migrados a modal centrado** (quitado `variant="flyout"`, ancho `max-w-lg` → `max-w-2xl`):
  - [`resources/views/livewire/user/create-user-modal.blade.php`](../../../../resources/views/livewire/user/create-user-modal.blade.php)
  - [`resources/views/livewire/user/edit-user-modal.blade.php`](../../../../resources/views/livewire/user/edit-user-modal.blade.php)
- **Grilla de 2 columnas** (`sm:grid-cols-2`): Nombre | Correo, Rol | Sede. Dependencias (chips) y
  Contraseña ocupan ancho completo (`sm:col-span-2`). En `sm` baja a 1 columna (móvil cómodo).
- En `edit-user-modal` se quitaron los hacks de altura del flyout (`-m-6 p-6 min-h-full`).
- **Lógica Livewire intacta** (mismos `wire:model`, `@error`, condicionales y `wire:key`).
- **Tests:** `tests/Feature/Users/UserCreateTest.php` y `UserUpdateTest.php` siguen verdes (31).

### Inventario de `variant="flyout"` (hallazgo)
La ADR asumía que los modales de **eventos** ya eran centrados, pero **no lo son**: siguen como
flyout. Quedan fuera de este cambio (alcance = formularios de usuario), pero conviene unificarlos
para la consistencia que persigue esta ADR:

| Archivo | ¿Migrado? |
|---|---|
| `user/create-user-modal.blade.php` | ✅ sí |
| `user/edit-user-modal.blade.php` | ✅ sí |
| `event/create-event-modal.blade.php` | ✅ sí (centrado; quitar `flyout` también eliminó el `overflow-y-auto` que **recortaba los desplegables** en pantallas pequeñas) |
| `event/edit-event-modal.blade.php` | ⬜ pendiente (flyout) |
| `event/attendees-modal.blade.php` | ⬜ es un listado, no un formulario — probablemente dejar como flyout |

> **Nota sobre desplegables:** el recorte de opciones que se veía en los formularios **no** era
> `overflow-hidden` propio nuestro, sino el `overflow-y-auto` que Flux añade **solo** a la variante
> `flyout` (no a la centrada). Al centralizar, el `<dialog>` centrado usa `overflow: visible` y el
> panel del `searchable-select` ya no se recorta. Teletransportarlo al `body` **no** sería opción:
> los `flux:modal` usan `<dialog showModal()>` (top layer) y el panel quedaría por detrás.

## Relacionado
[[convenciones]] · [[mapa-de-modulos]]
