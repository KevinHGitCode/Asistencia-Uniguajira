---
tipo: adr
descripcion: ADR-0010 (propuesta) — Mejoras al módulo de usuarios: diseño, usuarios activos en vivo y estadísticas de uso
actualizado: 2026-06-20
---

# ADR-0010 · Mejoras al módulo de usuarios

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-20
- **Contexto del repo:** `UserController` (`index/show/information/create/edit`),
  `app/Livewire/User/*` (`Card`, `Avatar`, `CreateUserModal`, `EditUserModal`),
  vistas `resources/views/users/*`. Sesiones en BD (`SESSION_DRIVER=database`) y acciones
  `login`/`logout` ya registradas en `activity_logs`. Roles `user/admin/superadmin`.

## Contexto
El módulo de usuarios funciona pero su **vista es la más austera** del sistema (no usa del todo
los patrones nuevos: tablas con búsqueda, modales centrados, selector con búsqueda). Además, un
administrador hoy **no puede ver quién está activo** ni métricas de uso por usuario.

## Decisión propuesta
Tres frentes, coordinados:

1. **Diseño/UX:** alinear `users/index` y el detalle con el resto de la administración (tabla con
   buscador en vivo, badges de rol/sede, modales **centrados** — liga a
   [[adr-0006-formularios-en-modal-centrado]]) y selector con búsqueda en formularios.
2. **Usuarios activos en (casi) tiempo real:** derivarlos de la tabla **`sessions`** (ya que
   `SESSION_DRIVER=database`): `user_id` con `last_activity` reciente (p. ej. < 5 min). Un
   indicador con **poll corto** (`wire:poll.10s` o endpoint ligero) da "lo más tiempo real
   posible" **sin** websockets ni infra extra. (Si más adelante se quiere instantáneo, evaluar
   broadcasting; fuera de alcance ahora.)
3. **Estadísticas de uso por usuario:** tiempo en la app (de pares `login`/`logout` en
   `activity_logs` o de `sessions`), nº de eventos creados, última actividad, acciones por
   módulo. Mostrarlas en el detalle del usuario.

## Consecuencias
- ➕ Panel de usuarios más útil y consistente; visibilidad operativa (quién está, qué hacen).
- ➕ Aprovecha datos que **ya existen** (`sessions`, `activity_logs`) → poca o nula migración.
- ➖ El "tiempo real" por poll tiene latencia (segundos) y carga periódica; elegir intervalo.
- ➖ El "tiempo en la app" derivado de login/logout es **aproximado** (cierres sin logout); puede
   requerir un heartbeat o usar `last_activity`.
- 🔁 Posible columna/índice para acelerar consultas de actividad; revisar multi-sede
   ([[migracion-multi-sede]]) en los listados por sede.

## Alternativas consideradas
- **Broadcasting/websockets** para presencia instantánea: mejor UX pero infra extra (no encaja en
  hosting compartido de Hostinger ahora).
- **No medir**: se pierde visibilidad operativa que el admin pidió.

## Pendiente para aceptar
- [ ] Definir umbral de "activo" (min) e intervalo de poll.
- [ ] Decidir métricas exactas del detalle de usuario y su fuente (sessions vs activity_logs).
- [ ] Rama sugerida: `feat/modulo-usuarios-mejoras` (🟢 UI + lectura; 🔴 si se añade columna/índice).

## Relacionado
[[mapa-de-modulos]] · [[personas-y-roles]] · [[adr-0006-formularios-en-modal-centrado]]
