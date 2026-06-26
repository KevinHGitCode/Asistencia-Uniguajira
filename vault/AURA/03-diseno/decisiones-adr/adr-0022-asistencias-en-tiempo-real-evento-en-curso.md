---
tipo: adr
descripcion: ADR-0022 (propuesta) — Asistencias que suben en vivo (sin recargar) e indicador de evento en curso
actualizado: 2026-06-25
---

# ADR-0022 · Asistencias en tiempo real e indicador de evento en curso

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** detalle de evento (`EventController::show`, `resources/views/events/`,
  `resources/js/events/`), registro público `App\Livewire\Event\AttendanceRegistration`,
  patrón de poll sin websockets ya usado en `OnlineCount` / `NotificationBell`.

## Contexto
Cuando un responsable tiene abierto el detalle de un evento, las asistencias **no se actualizan
solas**: hay que **recargar** la página para ver los nuevos registros. Durante un evento en vivo eso
resta sensación de "está pasando ahora".

## Decisión
Refrescar el detalle del evento **en vivo** mientras el evento está abierto, **sin recargar**:

1. **Conteo y gráficas en vivo:** poll corto (estilo `OnlineCount`/`NotificationBell`, sin
   websockets por Hostinger). El contador **sube con animación** y las barras/gráficas **crecen**
   suavemente al llegar nuevos registros.
2. **Indicador de "evento en curso":** un **reloj** cuyas manecillas avanzan por 4 posiciones
   (12 · 3 · 6 · 9) en bucle, visible **solo** mientras el evento está abierto
   (`isOpenForAttendance()`), para comunicar de un vistazo que está transcurriendo.

## Consecuencias
- ➕ Sensación de **tiempo real**: el responsable ve sumar las asistencias sin tocar nada.
- ➕ El indicador de evento en curso da contexto inmediato.
- ➖ Poll periódico = algo de carga (mitigable: solo cuando el evento está abierto y la pestaña
   visible — `Page Visibility API`).
- 🔁 Reutiliza el patrón de poll existente; las animaciones van en el front (Alpine/React).

## Alternativas consideradas
- **WebSockets (Echo/Reverb)** — tiempo real exacto, pero requiere servidor persistente; **inviable
  en Hostinger compartido** (mismo criterio que [[adr-0018-centro-de-notificaciones-in-app]]).
- **Recarga manual (actual)** — sin costo, pero sin la experiencia "en vivo".

## Relacionado
[[adr-0018-centro-de-notificaciones-in-app]] · [[registro-de-asistencia]] · [[mapa-de-modulos]]
