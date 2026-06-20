---
tipo: historia-usuario
descripcion: HU sembrada desde el repo — registro de asistencia por QR (flujo real)
actualizado: 2026-06-20
---

# HU-0001 · Registrar asistencia por QR

> Ejemplo real sembrado desde el código (`AttendanceRegistration`). Plantilla: [[plantilla-historia-de-usuario]].

**Como** asistente a un evento (estudiante, docente o comunidad externa),
**quiero** registrar mi asistencia escaneando el QR e ingresando mi documento,
**para** quedar registrado sin necesidad de crear una cuenta.

## Contexto / valor
Sustituye la planilla en papel y alimenta los reportes PDF y las estadísticas. Es el flujo
más usado del sistema. Ver [[registro-de-asistencia]].

## Criterios de aceptación
- [x] Accedo por `GET /events/acceso/{slug}` y veo los datos del evento.
- [x] Debo **aceptar el tratamiento de datos** antes de continuar.
- [x] Puedo buscarme por **documento o código estudiantil**.
- [x] Si **existo**, selecciono estamento/rol cuando tengo varios y confirmo con mi detalle
      demográfico (género y grupo priorizado obligatorios).
- [x] Si **no existo** y soy externo, me doy de alta con nombre, apellido, email opcional y
      **organización**.
- [x] Si **ya registré** asistencia a este evento, veo aviso de duplicado con la hora.
- [x] Si el evento **no ha iniciado** o **ya terminó** (`ended_at`/`end_time`), no puedo
      registrar.
- [x] Al confirmar, veo pantalla de éxito con la hora y mi total de asistencias; si tengo
      email, recibo correo de confirmación.

## Estado
✅ **Implementada** en `app/Livewire/Event/AttendanceRegistration.php`.

## Notas técnicas
- Anti-duplicado atómico (transacción + índice único). Detalle en [[registro-de-asistencia]].
- ⚠️ Existe un flujo legacy paralelo en `AttendanceController` que **no** debe usarse
  ([[adr-0003-retirar-flujo-legacy-de-asistencia]]).

## Pruebas relacionadas
Ver [[estrategia-de-pruebas]] (no hay aún test de feature específico de este componente —
candidato a añadir).
