---
tipo: adr
descripcion: ADR-0019 (propuesta) — Solicitud de corrección de datos del participante con cupo de intentos
actualizado: 2026-06-25
---

# ADR-0019 · Solicitud de corrección de datos del participante

- **Estado:** 🟡 Propuesta
- **Fecha:** 2026-06-25
- **Contexto del repo:** `app/Livewire/Event/AttendanceRegistration.php` (registro público por QR),
  modelos `Participant` / `ParticipantRole` / `AttendanceDetail`, ruta pública `events.access`,
  rate limiting existente ([[adr-0005-rate-limiting-anti-abuso]]).

## Contexto
Al registrar asistencia, una persona puede haber quedado con **datos incorrectos** (nombre mal
escrito, correo equivocado, etc.). Hoy no tiene forma de pedir que se corrijan: dependería de
contactar a un administrativo. Pero permitir edición libre de datos de identidad desde el QR público
sería riesgoso (suplantación, vandalismo).

## Decisión
Ofrecer un **enlace sutil** ("¿Algún dato incorrecto? Solicitar corrección") en las pantallas del
flujo público (p. ej. `found` / `success` / `duplicate`). Al abrirlo, el participante propone los
valores corregidos de un **subconjunto acotado de campos** (nombre, apellido, correo; no documento).
Esto **crea una solicitud** (`data_correction_requests`) que un administrativo **revisa y aprueba**;
no edita la BD directamente.

- **Cupo:** máximo **3 solicitudes por participante cada 7 días** (anti-abuso), reutilizando la
  estrategia de [[adr-0005-rate-limiting-anti-abuso]].
- **Estados:** `pendiente` → `aprobada` / `rechazada`. Al aprobar, se aplican los cambios y queda
  traza en `activity_logs`.
- (Opcional) primer consumidor del centro de notificaciones [[adr-0018-centro-de-notificaciones-in-app]]:
  avisar al admin que hay correcciones pendientes.

## Consecuencias
- ➕ El participante puede **arreglar sus datos** sin trámite externo; mejora la calidad de la BD.
- ➕ Sin riesgo de edición pública directa: pasa por **revisión humana**.
- ➖ Nueva tabla + UI de revisión (🔴 migración) y un módulo de administración más.
- 🔁 Decidir qué campos son corregibles y si algunos (p. ej. teléfono) podrían auto-aplicarse.

## Alternativas consideradas
- **Edición directa desde el QR** — rápida pero insegura (suplantación); descartada.
- **Solo por contacto manual al admin** — es el estado actual; no escala.

## Pendiente para aceptar
- [ ] Campos corregibles exactos y cuáles (si alguno) se auto-aplican.
- [ ] Cómo identificar de forma segura al solicitante (¿documento + último evento?).

## Relacionado
[[hu-0001-registro-asistencia-qr]] · [[hu-0002-no-repetir-datos-en-cada-asistencia]] ·
[[registro-de-asistencia]] · [[modelo-de-datos]] · [[adr-0005-rate-limiting-anti-abuso]]
