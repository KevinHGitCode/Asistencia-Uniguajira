---
tipo: adr
descripcion: ADR-0015 (implementado) — Estadísticas del detalle de evento en vivo (auto-refresco por polling)
actualizado: 2026-06-24
---

# ADR-0015 · Estadísticas del evento en vivo (polling)

- **Estado:** 🟢 Implementado (2026-06-24)
- **Fecha:** 2026-06-24
- **Contexto del repo:** isla React de gráficos del detalle de evento
  (`resources/js/events/EventChartsApp.jsx`, montada por `events/index.jsx` en
  `resources/views/events/show.blade.php`). Consume `/api/statistics/event/{event}/*`.

## Contexto
Las estadísticas del detalle de un evento (asistentes por estamento / programa / dependencia /
organización / sexo / grupo) se cargaban **una sola vez al montar** la isla React. Durante un
evento en curso, cada asistencia registrada por QR **no** se reflejaba hasta **recargar la página**.
Además, si el evento arrancaba con 0 asistentes, la isla **ni siquiera se montaba** (el Blade solo
la renderizaba con `asistenciasCount > 0`), así que la primera asistencia no aparecía sola.

## Decisión
Refrescar las estadísticas **automáticamente mientras el evento está abierto**, por **polling**
(no websockets — coherente con [[adr-0010-mejoras-modulo-usuarios]] y el hosting compartido):

- La isla React hace **poll cada 20 s** (`POLL_MS`) mientras el evento esté abierto
  (`isOpenForAttendance()`), pasado a React vía `data-event-open`.
- Refresco **silencioso**: no muestra el spinner ni pisa los datos actuales si una petición falla;
  usa `AbortController` y **se pausa con la pestaña oculta** (`document.hidden`).
- Se monta la isla cuando el evento **inició y (tiene asistentes _o_ sigue abierto)**; con 0
  asistentes en curso muestra un estado **"Esperando asistentes"** que se llena solo.
- Indicador **"● En vivo · se actualiza automáticamente · hh:mm:ss"** cuando el evento está abierto.
- Un evento **finalizado** no hace polling (datos estáticos); finalizado sin asistentes mantiene el
  mensaje server-side "No hay estadísticas".

## Consecuencias
- ➕ El organizador ve las estadísticas actualizarse durante el evento sin recargar.
- ➕ Reutiliza el endpoint y la isla existentes; cero cambios de esquema.
- ➕ Respeta el rate limit `api-stats` (6 req/20 s ≈ 18/min, muy por debajo de 300/min).
- ➖ No es push instantáneo "por cada asistencia": hay hasta 20 s de latencia. Push real exigiría
  broadcasting/websockets (fuera de alcance en Hostinger compartido).
- 🔁 Cambió la condición de montaje de la isla en `events/show.blade.php`.

## Alternativas consideradas
- **Broadcasting/websockets** (push por asistencia): instantáneo pero requiere infra que el hosting
  compartido no ofrece hoy. Mismo criterio que ADR-0010.
- **Dejarlo como estaba** (recargar a mano): la fricción que se pidió resolver.

## Implementación (2026-06-24)
- [x] `EventChartsApp.jsx`: `fetchData(signal, { silent })` reutilizable; `useEffect` de carga
  inicial + `useEffect` de `setInterval` cuando `open`; pausa en pestaña oculta; `AbortController`;
  `updatedAt`; estado "Esperando asistentes"; badge `LiveBadge` "En vivo".
- [x] `events/index.jsx`: lee `data-event-open` y lo pasa como prop `open` (mount inicial +
  `livewire:navigated`).
- [x] `events/show.blade.php`: monta la isla cuando `asistenciasCount > 0 || ! $eventHasEnded` y
  añade `data-event-open`.
- Verificado: `EventBreadcrumbContextTest` y `EventCampusAccessTest` en verde; build OK.

## Pendiente / posible mejora
- [ ] El **listado/contador de asistentes** (`event.attendees-modal`, Livewire) aún no se
  auto-refresca; se podría añadir `wire:poll` mientras el evento esté abierto.
- [ ] Si se quisiera instantáneo, evaluar broadcasting (fuera de alcance).

## Relacionado
[[adr-0010-mejoras-modulo-usuarios]] · [[adr-0001-react-islands-estadisticas]] · [[registro-de-asistencia]]
