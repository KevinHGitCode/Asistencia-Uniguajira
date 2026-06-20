---
tipo: nota-modulo
descripcion: Módulo de registro público de asistencia por QR (el flujo Livewire real)
actualizado: 2026-06-20
---

# Módulo: Registro de asistencia (QR público)

> Ejemplo real sembrado desde el repo. Es el flujo central del producto.

## Qué hace
Permite a un asistente registrar su asistencia a un evento escaneando un **QR público**, sin
iniciar sesión. Captura la identidad del participante y un **detalle demográfico por
asistencia**.

## Dónde vive (código)
- Componente: `app/Livewire/Event/AttendanceRegistration.php`
- Vista contenedora: `resources/views/events/access.blade.php` (monta el componente con `:slug`)
- Vista del componente: `resources/views/livewire/event/attendance-registration.blade.php`
- Ruta pública: `GET /events/acceso/{slug}` → `EventController::access` (`events.access`)
- Correo: `app/Mail/AttendanceRegisteredMail.php`

## Flujo (máquina de estados `step`)
`search` → (no existe) `register_external` → `details` → `success`
`search` → (existe) `found` → [`select_type`] → [`select_role`] → `details` → `success`
Estados especiales: `duplicate`, `closed`, `event_ended`.

## Reglas de negocio clave
- Búsqueda por **documento o `student_code`**.
- **Comunidad externa**: si el documento no existe, alta rápida con nombre, apellido, email
  opcional y **organización** (autocompletado + `firstOrCreate` case-insensitive). Se crea un
  `ParticipantRole` con tipo "Comunidad Externa".
- **Tratamiento de datos**: hay que aceptarlo (`acceptsDataTreatment`) para buscar.
- **Anti-duplicado atómico**: verificación dentro de una transacción con `lockForUpdate` +
  índice único `(event_id, participant_id)`. Captura `UniqueConstraintViolationException`.
- **Detalle**: género y grupo priorizado obligatorios; precarga el último detalle del
  participante (`loadLastDefaults`).
- **Estado del evento**: respeta `hasNotStarted()`, `isOpenForAttendance()`, `ended_at`.
- **Correo**: se envía fuera de la transacción; si falla, no rompe el registro (solo log).
- **Auditoría**: registra en `activity_logs` vía `ActivityLogService`.

## Datos que toca
`participants`, `participant_roles`, `organizations`, `attendances`, `attendance_details`.
Ver [[modelo-de-datos]].

## Gotchas / deuda
- Existe un **flujo legacy** equivalente y más pobre (`AttendanceController`) que ya no se usa
  → [[brechas-conocidas]] #1 y [[adr-0003-retirar-flujo-legacy-de-asistencia]].

## Relacionado
- Historia: [[hu-0001-registro-asistencia-qr]] · Personas: [[personas-y-roles]]
