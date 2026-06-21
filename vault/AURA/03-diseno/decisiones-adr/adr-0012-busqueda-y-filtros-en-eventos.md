---
tipo: adr
descripcion: ADR-0012 (propuesta) — Búsqueda y filtros en "Todos los eventos" y en los eventos del detalle de usuario
actualizado: 2026-06-20
---

# ADR-0012 · Búsqueda y filtros en eventos

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `resources/views/events/admin-events.blade.php` ("Todos los eventos",
  ruta `admin.events.index`) y `resources/views/users/information.blade.php` (eventos del usuario)
  **no tienen buscador ni filtros**. `events/list.blade.php` ("Tus eventos") conviene revisar.

## Contexto
Las vistas que listan eventos para administración y en el detalle de un usuario muestran los
eventos **sin ninguna forma de buscar o filtrar**. Con muchos eventos, encontrar uno concreto
obliga a scrollear toda la lista.

## Decisión propuesta
Añadir **búsqueda + filtros** a esas listas, del lado del servidor:

- **Búsqueda de texto** por título / ubicación.
- **Filtros**: rango de **fechas**, **dependencia/área**, **estado** (abierto / finalizado /
  próximo) y **creador** (en "Todos los eventos"); por **sede** cuando aplique multi-sede.
- UI consistente con el resto (input de búsqueda con debounce + `x-ui.searchable-select` para los
  filtros); estado reflejado en la URL.
- Aplicarlo primero en "Todos los eventos" y en los eventos del detalle de usuario; revisar
  "Tus eventos" para homogeneizar.

## Consecuencias
- ➕ Encontrar eventos deja de depender del scroll; mejor para administración y soporte.
- ➕ Reutiliza componentes y patrones existentes.
- ➖ Requiere paginar/filtrar en servidor (algunas de estas vistas hoy no paginan); cuidar
  rendimiento e índices por fecha/dependencia.
- 🔁 Coordinar con multi-sede: el filtro/listado debe respetar `CampusScopeService`
  ([[migracion-multi-sede]]).

## Alternativas consideradas
- **Filtrar en cliente (Alpine)** sobre la lista ya cargada: simple pero no escala.
- **No hacerlo**: persiste la fricción actual.

## Pendiente para aceptar
- [ ] Confirmar qué vistas entran (admin-events, detalle de usuario, ¿"Tus eventos"?).
- [ ] Definir filtros y paginación por vista.
- [ ] Rama sugerida: `feat/eventos-busqueda-filtros` (🟢 lectura/UI).

## Relacionado
[[mapa-de-modulos]] · [[adr-0013-breadcrumbs-detalle-evento]] · [[adr-0011-mejores-filtros-en-participantes]]
