---
tipo: adr
descripcion: ADR-0011 (propuesta) — Mejores filtros en el listado de participantes
actualizado: 2026-06-20
---

# ADR-0011 · Mejores filtros en Participantes

- **Estado:** 🟡 Propuesta (sin iniciar; ya existe el componente `select` buscable reutilizable —
  commits `8fa8623`/`e076e35` — que reduce el costo de la UI de filtros)
- **Fecha:** 2026-06-20 (actualizado 2026-06-24)
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

## Pendiente para aceptar
- [ ] Lista final de filtros y su comportamiento combinado (AND).
- [ ] Índices necesarios en `participant_roles`.
- [ ] Rama sugerida: `feat/participantes-filtros` (🟢 si no toca esquema; 🔴 si añade índices).

## Relacionado
[[mapa-de-modulos]] · [[modelo-de-datos]] · [[adr-0008-listado-participantes-en-react]]
