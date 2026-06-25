---
tipo: adr
descripcion: ADR-0012 (propuesta) — Búsqueda y filtros en "Todos los eventos" y en los eventos del detalle de usuario
actualizado: 2026-06-20
---

# ADR-0012 · Búsqueda y filtros en eventos

- **Estado:** 🟢 Implementado (búsqueda + filtros estructurados de cliente)
- **Fecha:** 2026-06-20
- **Implementado:** 2026-06-21 (búsqueda) · 2026-06-24 (filtros estructurados)
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

## Implementación (MVP)

En vez de repetir buscador+lista en cada vista, se creó un **contenedor reutilizable de grupo de
eventos con búsqueda integrada**. Solo se le pasa *qué eventos* incluir y el buscador filtra **ese**
grupo (por título / ubicación / dependencia / creador, sin acentos).

- **Rama:** `feat/eventos-busqueda-filtros`.
- **Componentes nuevos:**
  - [`<x-events.card>`](../../../../resources/views/components/events/card.blade.php) — tarjeta de
    evento única (elimina el markup duplicado ~5 veces). Expone `data-event-card` + `data-search`.
  - [`<x-events.group>`](../../../../resources/views/components/events/group.blade.php) — contenedor
    con encabezado, **buscador** y grilla; estados vacío y "sin resultados".
  - [`events-group.js`](../../../../resources/js/components/events-group.js) — filtro de cliente
    Alpine, reutiliza `normalizar` de `text-filter` (igual que la paleta de comandos / selectores).
- **Aplicado en:**
  - `events/list.blade.php` ("Tus eventos"): los 4 grupos (en proceso / próximos / finalizados /
    dependencia) — colecciones, el buscador funciona sobre todo el grupo.
  - `users/information.blade.php`: eventos propios + por dependencia. Se cambió el controlador de
    `paginate(6)` a colecciones para que el buscador cubra todo el conjunto, conservando el origen
    del breadcrumb (`?from=usuario&user_id=…`, [[adr-0013-breadcrumbs-detalle-evento]]).
- **Tests:** `tests/Feature/Event/EventsGroupComponentTest.php` (buscador + tarjetas + origen);
  `UserShowTest`, `EventCampusAccessTest`, `EventBreadcrumbContextTest` siguen en verde.

### Decisión de enfoque: filtro de cliente vs. servidor
La ADR planteaba filtros **server-side**. Para estas vistas Blade los eventos ya se cargan como
**colección completa** (no escalan a millones; son por usuario/dependencia/semestre), así que el
filtro de cliente por contenedor es suficiente, instantáneo y mucho más simple. Para **"Todos los
eventos"** (`admin-events`, React) el filtrado **ya es server-side** (`useAdminEventos`), así que
queda fuera de este contenedor.

## Filtros estructurados (2026-06-24)
Se extendió el **mismo contenedor de cliente** (sin servidor, coherente con el MVP):
- **`<x-events.card>`** ahora expone `data-date` (`Y-m-d`), `data-status` (`abierto`/`proximo`/
  `finalizado`, derivado de `hasNotStarted()`/`isOpenForAttendance()`/`ended_at`), `data-dependency`,
  `data-area` y `data-creator`.
- **`<x-events.group>`** añade un botón **"Filtros"** (con badge de filtros activos) y un panel
  colapsable: **rango de fechas** (Desde/Hasta) siempre, y selects de **Estado**, **Dependencia**,
  **Área** y **Creador** que solo se renderizan si el grupo tiene **más de un** valor distinto
  (opciones derivadas de la propia colección). Botón "Limpiar filtros".
- **`events-group.js`**: estado Alpine (`q`, `dateFrom`, `dateTo`, `status`, `dependency`, `area`,
  `creator`) con `$watch` que re-aplica el filtrado combinado (AND) sobre las tarjetas; `countLabel`
  y `activeFilterCount` reactivos. Sin persistencia en URL (igual que la búsqueda).
- **Tests:** `EventsGroupComponentTest` verifica los `data-*` y el botón de filtros.

> Nota: sigue siendo filtrado de cliente por contenedor (colecciones acotadas). "Todos los eventos"
> (`admin-events`, React) ya filtra server-side aparte y queda fuera.

### Fuera del MVP / futuro
- (Si alguna lista creciera mucho) mover esa vista a filtrado server-side, como participantes (ADR-0008).
- **Participantes (ADR-0011):** es otro modelo de datos y ya tiene su propio listado con búsqueda
  y filtros (`ParticipantsList`). Este contenedor es de **eventos**; 0011 sigue aparte.

## Pendiente para aceptar
- [x] Confirmar qué vistas entran → "Tus eventos" y detalle de usuario (admin-events es React).
- [x] Rama: `feat/eventos-busqueda-filtros`.
- [x] Filtros estructurados además de la búsqueda de texto (2026-06-24).

## Relacionado
[[mapa-de-modulos]] · [[adr-0013-breadcrumbs-detalle-evento]] · [[adr-0011-mejores-filtros-en-participantes]]
