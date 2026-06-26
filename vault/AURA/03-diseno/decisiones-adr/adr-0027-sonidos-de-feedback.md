---
tipo: adr
descripcion: ADR-0027 (propuesta) — Sonidos de feedback (notificación, registro de asistencia) opcionales y respetuosos
actualizado: 2026-06-25
---

# ADR-0027 · Sonidos de feedback

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** registro público `App\Livewire\Event\AttendanceRegistration` (paso
  `success`), campana `App\Livewire\NotificationBell` ([[adr-0018-centro-de-notificaciones-in-app]]).

## Contexto
La app no emite **sonido** en acciones clave. Un sonido breve y agradable refuerza el feedback
(confirma que algo pasó) y aporta sensación de **calidad**, especialmente útil cuando el operador no
está mirando fijamente la pantalla (p. ej. en la mesa de registro de un evento).

## Decisión
Añadir **sonidos cortos** en momentos puntuales:
- **Registro de asistencia exitoso** (en la pantalla pública de QR).
- **Llegada de una notificación** in-app (campana).

Con estas reglas:
- **Opt-in / configurable:** preferencia para silenciar (respetar `prefers-reduced-motion`/sonido y
  recordar la elección en `localStorage`).
- **No intrusivo:** volumen bajo, sin repetición molesta; respetar la política de autoplay del
  navegador (reproducir tras interacción del usuario).

## Consecuencias
- ➕ Feedback más claro y percepción de **calidad**.
- ➕ Útil en mesas de registro con mucho movimiento.
- ➖ Mal usados, los sonidos molestan → obligatorio el control para silenciar.
- 🔁 Sumar 2-3 assets de audio ligeros y un pequeño servicio de reproducción en el front.

## Alternativas consideradas
- **Sin sonido (actual)** — neutro pero menos expresivo.
- **Solo vibración (móvil)** — complementaria, no sustituye en escritorio.

## Relacionado
[[adr-0018-centro-de-notificaciones-in-app]] · [[hu-0001-registro-asistencia-qr]] · [[convenciones]]
