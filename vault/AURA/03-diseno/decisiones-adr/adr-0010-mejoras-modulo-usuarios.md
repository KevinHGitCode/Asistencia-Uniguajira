---
tipo: adr
descripcion: ADR-0010 (implementado) — Mejoras al módulo de usuarios: diseño, usuarios activos en vivo y estadísticas de uso
actualizado: 2026-06-24
---

# ADR-0010 · Mejoras al módulo de usuarios

- **Estado:** 🟢 Implementado — frentes 2 y 3 (2026-06-24); frente 1 (diseño/UX) parcial
- **Fecha:** 2026-06-20 (implementado 2026-06-24)
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

## Progreso (2026-06-24)
**Frente 1 — diseño/UX:**
- [x] Modal de edición de usuario con los **datos actuales** precargados (commit `5b0afcb`).
- [x] Mejora visual al editar usuario (commit `a515c53`).
- [x] Las dependencias ya no concatenan su sede en el selector (ya viene en el nombre) (`5b0afcb`).
- [ ] Alinear por completo `users/index` y el detalle con el resto de la administración (tabla con
  buscador en vivo, badges de rol/sede).

## Implementación frentes 2 y 3 (2026-06-24)
Nuevo servicio `app/Services/UserActivityService.php` (sin migraciones; lee `sessions` y `activity_logs`):
- **Decisiones:** umbral "en línea" = **5 min** (`ONLINE_THRESHOLD_SECONDS = 300`); poll = **30s**;
  "último acceso" desde `sessions.last_activity` (no `users.updated_at`).

**Frente 2 — usuarios activos en vivo:**
- [x] `onlineUserIds()` / `onlineCount()` / `isOnline()` desde `sessions` (`last_activity` reciente).
- [x] Componente Livewire `User\OnlineCount` (`wire:poll.30s="refresh"`) con badge "N usuarios en
  línea" en el header; en cada poll emite el evento `online-users-updated` con los IDs en línea.
- [x] `UserController::index` pasa `$onlineUserIds` (snapshot inicial); en `users/index` cada avatar
  (desktop + móvil) muestra un **punto verde en vivo**: Alpine inicializa `onlineIds` con el snapshot
  y lo actualiza al escuchar `online-users-updated`, así el punto **aparece/desaparece sin recargar**.

**Frente 3 — estadísticas de uso por usuario** (en `users/information`):
- [x] `usageFor($user)`: estado en línea, última actividad, nº de inicios de sesión, último inicio,
  **tiempo aprox. en la app** (suma de pares login→logout de `activity_logs`, módulo `sesion`) y
  **acciones por módulo**.
- [x] Panel "Actividad y uso" con tarjetas + badges por módulo; se corrigió "Último acceso" para
  usar `sessions` en vez de `updated_at`.

**Frente 1 — diseño/UX (avance 2026-06-24):**
- [x] `users/index`: padding del contenedor (`p-1 sm:p-4 md:p-6`), **buscador compacto** (estilo de
  `x-events.group`, ~`sm:w-72`, `py-1.5 text-sm` + ícono), botón **"Crear usuario" a solo ícono**
  (`flux:button icon="user-plus" square`), y la leyenda Rol/Dependencias pasó a un **dropdown
  informativo (ⓘ)** en el header (mismo patrón que participantes). Paginación ya existía
  (`$users->links()`).
- [x] **Activar/Desactivar** se movió del detalle (botón flotante) al **modal de edición**, como un
  **selector Estado (Activo/Inactivo)** que se aplica **al guardar** (no un botón rojo/verde); se
  eliminó el componente `Admin\ToggleUserActive`. Jerarquía (validada en servidor): **superadmin**
  gestiona a cualquiera (usuarios/admins/superadmins); **admin** solo a usuarios normales de su sede;
  nadie a sí mismo. Pruebas en `tests/Feature/Livewire/UserEditModalToggleTest.php`.
- [x] Refinamientos visuales: columna **Estado** y el estado del detalle pasaron de badge con fondo a
  **texto de color** (verde/rojo); el **flash** de éxito se rediseñó (emerald + ícono, dark-mode);
  el botón de **crear usuario** quedó **azul** (`#3b82f6`).

**Pruebas:** `tests/Feature/UserActivityServiceTest.php` (online + uso),
`tests/Feature/Livewire/UserOnlineCountTest.php` (contador + render índice/detalle),
`tests/Feature/Livewire/UserEditModalToggleTest.php` (jerarquía de activación).

## Pendiente / posible mejora
- [ ] Frente 1: si se quiere, llevar la tabla de usuarios a una isla React (como participantes,
  ADR-0008) para búsqueda/paginación sin recargar — opcional.
- [ ] El "tiempo en la app" es aproximado (cierres sin logout no cuentan); si se quiere exactitud,
  evaluar un heartbeat o derivarlo de `sessions`.
- [ ] Índice en `sessions.last_activity`/`user_id` si crece mucho (Laravel ya indexa por defecto).

## Relacionado
[[mapa-de-modulos]] · [[personas-y-roles]] · [[adr-0006-formularios-en-modal-centrado]]
