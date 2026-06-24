---
tipo: adr
descripcion: ADR-0008 (implementado) — Migrar el listado de participantes a una isla React por rendimiento
actualizado: 2026-06-24
---

# ADR-0008 · Listado de participantes en React (isla)

- **Estado:** 🟢 Implementado — **Opción A** (tabla React + modales Livewire) (2026-06-24)
- **Fecha:** 2026-06-20 (implementado 2026-06-24)
- **Contexto del repo:** `app/Livewire/Admin/ParticipantsList.php` +
  `resources/views/livewire/admin/participants-list.blade.php` (búsqueda, filtro “sin clasificar”,
  paginación 25, modal de edición). Patrón de islas React ya establecido en
  `resources/js/statistics/` ([[adr-0001-react-islands-estadisticas]]).

## Contexto
El **listado de participantes** puede crecer a muchos registros y es de los más pesados de la app.
Hoy es Livewire: cada interacción (búsqueda, paginación, filtros) hace ida y vuelta al servidor y
re-renderiza el DOM por morphing. El usuario pide migrar **solo este listado** a React para que sea
ligero y veloz — manteniendo el resto en Livewire (cambios a React **solo cuando se soliciten
explícitamente**).

## Decisión propuesta
Migrar **la tabla/listado** a una **isla React** (igual que estadísticas), dejando el resto del
módulo como está:

- **Isla React** montada en la pestaña “Lista de participantes”, consumiendo un **endpoint JSON
  nuevo** (`GET /api/participants`, con `auth + role:admin`) que haga **paginación, búsqueda,
  orden y filtros del lado del servidor**.
- **Rendimiento:** paginación/orden en servidor + posible **virtualización** de filas en cliente;
  fetch con `AbortController` (como en `resources/js/statistics/hooks/`).
- **Edición/eliminación:** decisión a afinar — opción A) mantener el **modal de edición en
  Livewire** y montar React solo para la tabla; opción B) llevar todo a React + API. Recomendado
  empezar por A) para acotar el cambio.
- Reutilizar el andamiaje de islas (`livewire:navigated` re-mount, tema oscuro por `classList`).

## Consecuencias
- ➕ Listado **más ligero y rápido** con grandes volúmenes (paginación server-side + virtualización).
- ➕ Coherente con la decisión ya tomada de usar React para vistas data-intensivas.
- ➖ Duplica algo de lógica (filtros/orden) en JS y añade un **endpoint API** con su autenticación.
- ➖ Suma peso de bundle (ya hay React+Recharts; reutilizar React reduce el costo marginal).
- ➖ Hay que **reconciliar** la convivencia con el modal de edición Livewire si se elige la opción A.
- 🔁 Nuevo contrato de API (`/api/participants`) → coordinar como cambio que serializa.

## Alternativas consideradas
- **Optimizar el Livewire actual** (índices, `wire:model.live` acotado, menos columnas): mejora
  incremental sin reescribir, pero no alcanza la ligereza de una tabla React con virtualización.
- **Migrar todo el módulo a React**: mayor alcance del pedido (“solo el listado”).

## Implementación (2026-06-24) — Opción A
**Backend**
- `app/Http/Controllers/Api/ParticipantsController.php`: `index` (paginado + búsqueda + filtros AND
  sobre roles activos + correo + sin clasificar) y `filterOptions` (catálogos).
- `routes/api.php`: `GET /api/participants` y `/api/participants/filter-options` bajo
  `web + auth + role:admin,superadmin`. Participantes globales (sin filtro de sede).
- Pruebas: `tests/Feature/Api/ParticipantsApiTest.php` (autorización, paginación/meta, filtros, catálogos).

**Contrato**
```
GET /api/participants?page&perPage&search&estamento&programa&dependencia&vinculacion&correo=con|sin&sinClasificar=1
→ { data: [ { id, document, student_code, first_name, last_name, email,
              types[], programs[], dependencies[], affiliations[], has_unclassified_role } ],
    meta: { current_page, last_page, per_page, total, from, to } }
GET /api/participants/filter-options → { types, programs, affiliations, dependencies }
```

**Frontend (isla React)** — `resources/js/participants/`
- `index.jsx` (mount + soporte `wire:navigated`), entrada en `vite.config.js`.
- `ParticipantsApp.jsx`: búsqueda con debounce, filtros colapsables, paginación sutil, overlay de
  carga, sincronización de filtros en la URL (incluye el deep-link `?filtro=sin_clasificar`).
- `hooks/useParticipants.js` (`fetch` + `AbortController`), `components/` (SearchableSelect,
  FiltersPanel, ParticipantsTable, Pagination), `icons.jsx`.

**Edición/eliminación (Opción A — puente Livewire ↔ React)**
- El componente `Admin\ParticipantsList` quedó **solo con los modales** (se le retiró el listado/filtros).
- React abre los modales con `Livewire.dispatch('open-edit-participant' | 'open-delete-participant')`
  (listeners `#[On(...)]`); al guardar/eliminar, Livewire emite `participants-refresh` y React recarga.
- Pruebas del puente en `tests/Feature/Livewire/ParticipantsListTest.php`.

> Nota: los filtros de [[adr-0011-mejores-filtros-en-participantes]] se reimplementaron en React +
> API (la versión Livewire previa se retiró). Virtualización de filas: no necesaria con paginación
> de 25/página; queda como opción futura si crece el `perPage`.

## Pendiente / posible mejora
- [ ] Índices en `participant_roles` para los `whereHas` (compartido con ADR-0011) si crece el volumen.
- [ ] Si se quisiera, virtualizar filas o subir `perPage`.

## Relacionado
[[adr-0001-react-islands-estadisticas]] · [[mapa-de-modulos]] · [[arquitectura]]
