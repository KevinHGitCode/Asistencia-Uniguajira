---
tipo: adr
descripcion: ADR-0008 (propuesta) — Migrar el listado de participantes a una isla React por rendimiento
actualizado: 2026-06-20
---

# ADR-0008 · Listado de participantes en React (isla)

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
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

## Pendiente para aceptar
- [ ] Diseñar el contrato de `/api/participants` (filtros, orden, paginación, forma de la fila).
- [ ] Elegir opción A (modal Livewire) vs B (todo React) para edición.
- [ ] Rama sugerida: `feat/participantes-listado-react` (🔴 nuevo endpoint/contrato API).

## Relacionado
[[adr-0001-react-islands-estadisticas]] · [[mapa-de-modulos]] · [[arquitectura]]
