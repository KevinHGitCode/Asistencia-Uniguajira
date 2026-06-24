---
tipo: adr
descripcion: ADR-0011 (implementado) — Mejores filtros en el listado de participantes
actualizado: 2026-06-24
---

# ADR-0011 · Mejores filtros en Participantes

- **Estado:** 🟢 Implementado (2026-06-24) — sin filtro de Sede (ver nota)
- **Fecha:** 2026-06-20 (implementado 2026-06-24)
- **Contexto del repo:** `app/Livewire/Admin/ParticipantsList.php` +
  `resources/views/livewire/admin/participants-list.blade.php`. Hoy tiene **búsqueda** (nombre /
  documento / correo) y un filtro **"sin clasificar"**, con paginación de 25.

## Contexto
El listado de participantes solo permite buscar por texto y filtrar "sin clasificar". Con muchos
participantes, encontrar subconjuntos útiles (p. ej. estudiantes de un programa, o de una sede)
es difícil. Faltan filtros estructurados.

## Decisión propuesta
Añadir filtros combinables, aplicados **del lado del servidor** (sobre `participant_roles`):

- **Estamento** (tipo), **Programa**, **Dependencia**, **Afiliación**, y **Sede** (cuando aplique
  multi-sede).
- **Con/sin correo**, **con/sin roles activos**, y conservar el filtro **sin clasificar**.
- UI con el componente existente [[adr-0001-react-islands-estadisticas|patrones de UI]] →
  reutilizar `x-ui.searchable-select` / `x-ui.multi-searchable-select` para elegir valores.
- Mantener la búsqueda de texto actual; los filtros se reflejan en la URL (querystring) para
  poder compartir/volver.

## Consecuencias
- ➕ Búsqueda mucho más útil para gestión real (segmentar por programa/estamento/sede).
- ➕ Reutiliza componentes ya construidos (selector con búsqueda).
- ➖ Las consultas se vuelven más complejas (joins sobre `participant_roles`); cuidar **índices**
  y rendimiento (liga con [[adr-0008-listado-participantes-en-react]] si se migra a React).
- 🔁 Coordinar con multi-sede: el filtro por sede debe respetar `CampusScopeService` cuando exista
  para participantes (hoy son globales — ver [[modelo-de-datos]]).

## Alternativas consideradas
- **Solo mejorar la búsqueda de texto**: insuficiente para segmentar por entidad.
- **Filtrar en cliente**: no escala con muchos registros (debe ser server-side).

## Implementación (2026-06-24)
Todo en `ParticipantsList` (Livewire), server-side, sin tocar esquema:
- [x] Filtros: **Estamento**, **Programa**, **Dependencia**, **Vinculación** (los 4 reutilizan
  `x-ui.searchable-select` con catálogos cargados una vez en `mount`) + **Con/sin correo** +
  se conserva **Sin clasificar**.
- [x] Los 4 filtros de rol se combinan **AND dentro del mismo rol activo** (un solo
  `whereHas('activeRoles', …)`); el de correo aplica sobre `participants.email`.
- [x] Reflejados en la **URL** con `#[Url]` (`estamento`, `programa`, `dependencia`,
  `vinculacion`, `correo`); compatible con el deep-link previo `?filtro=sin_clasificar`.
- [x] Botón **Limpiar filtros**, indicador de filtros activos (computed `hasActiveFilters`),
  `resetPage()` al cambiar cualquier filtro y mensajes de estado vacío según filtros/búsqueda.
- [x] Pruebas en `tests/Feature/Livewire/ParticipantsListTest.php` (estamento, programa,
  con/sin correo, combinación AND, limpiar filtros).

> **Sede:** se **omitió** a propósito. Según [[migracion-multi-sede]] los `participants` son
> globales y la regla es "no filtrar participantes directamente por sede". Si se quisiera, habría
> que derivar la sede del `campus` del programa/dependencia del rol — queda como posible extensión.

## No implementado / posible deuda
- **Índices** en `participant_roles` (`participant_type_id`, `program_id`, `dependency_id`,
  `affiliation_id`): no se añadieron. Con volúmenes altos los `whereHas` podrían beneficiarse;
  evaluar si el listado se siente lento (liga con [[adr-0008-listado-participantes-en-react]]).

## Pendiente para aceptar
- [x] Lista final de filtros y su comportamiento combinado (AND).
- [ ] Índices necesarios en `participant_roles` (diferido; ver arriba).
- [x] Rama sugerida: `feat/participantes-filtros` (🟢 no toca esquema).

## Relacionado
[[mapa-de-modulos]] · [[modelo-de-datos]] · [[adr-0008-listado-participantes-en-react]]
