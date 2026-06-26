---
tipo: historia-usuario
descripcion: El asistente no repite sus datos en cada evento; solo confirma/ajusta los volátiles
actualizado: 2026-06-25
---

# HU-0002 · No repetir mis datos cada vez que asisto

**Como** asistente recurrente a eventos (estudiante, docente o comunidad externa),
**quiero** que la universidad **no me pida los mismos datos cada vez** que registro asistencia,
**para** registrarme en segundos y solo actualizar lo que cambia.

## Contexto / valor
La pregunta que motiva esta historia: *si la universidad ya tiene mis datos, ¿por qué debo
repetírselos en cada evento?* La app lo resuelve con una **base de datos que respalda** al
asistente: la primera vez se capturan sus datos; **a partir de ahí, registrar asistencia es mucho
más simple**. Los datos **volátiles** (teléfono, ciudad, barrio, dirección…) se muestran ya
**prellenados** con lo último que registró, y puede **cambiarlos opcionalmente** si cambiaron.

Es el corazón de la "propuesta de valor sin fricción" de la [[vision]] y complementa
[[hu-0001-registro-asistencia-qr]].

## Criterios de aceptación
- [x] La identidad (documento, nombre, correo, estamento/programa) se captura **la primera vez**;
      luego me identifico solo con **documento o código estudiantil**.
- [x] Al volver a registrarme, el detalle **volátil** (teléfono, ciudad, barrio, dirección, género,
      grupo priorizado) aparece **prellenado** con el de mi **última** asistencia.
- [x] Puedo **editar** esos datos volátiles antes de confirmar (no estoy obligado a dejarlos igual).
- [x] Cada asistencia guarda su **propio snapshot** del detalle (lo que cambie hoy no reescribe el
      histórico de asistencias anteriores).
- [x] Género y grupo priorizado se confirman en cada registro (campos obligatorios).

## Estado
🟢 **Implementada** — `App\Livewire\Event\AttendanceRegistration::loadLastDefaults()` prellena desde
el último `attendance_details`; el snapshot por asistencia está en
[[adr-0002-snapshot-demografico-attendance-details]].

## Notas técnicas
- El prellenado lee `AttendanceDetail` más reciente del participante (`latest()`), no datos del
  participante en sí: así se respeta el snapshot por evento.
- Datos "estables" viven en `participants` / `participant_roles`; datos "del momento" en
  `attendance_details`. Ver [[modelo-de-datos]].

## Pruebas relacionadas
Candidato a test de feature del flujo de re-registro (prellenado + edición). Ver
[[estrategia-de-pruebas]].
