---
tipo: historia-usuario
descripcion: El sistema distingue participante único de asistencias para que los reportes no inflen el conteo
actualizado: 2026-06-25
---

# HU-0003 · Distinguir participante único de asistencias

**Como** administrativo que arma informes,
**quiero** que el sistema **distinga claramente entre participantes y asistencias**,
**para** que el consolidado **no infle el conteo** cuando una misma persona asiste a varios eventos.

## Contexto / valor
Problema clásico de las planillas: una misma persona que asiste a 10 eventos terminaba contada como
**10 asistentes** en el consolidado. La app lo corrige con dos conceptos separados:

- **Participante** = la persona, **única**. Asista a 1 o a 100 eventos, **cuenta como uno solo**.
- **Asistencia** = el acto de presentarse a un evento. **Puede repetirse**: un participante puede
  tener **muchas** asistencias.

Así los informes pueden decir, sin ambigüedad, "*N personas distintas*" frente a "*M asistencias
registradas*". Es la base de la "visión analítica" de la [[vision]].

## Criterios de aceptación
- [x] Un participante es **único** por documento (no se duplica al re-registrarse).
- [x] No se puede registrar **dos veces** la asistencia del mismo participante al **mismo** evento
      (aviso de duplicado con la hora).
- [x] El mismo participante **sí** puede tener asistencias en **eventos distintos**.
- [x] Las estadísticas pueden contar **personas distintas** (participantes) por separado de
      **asistencias** totales.
- [x] Al confirmar, el asistente ve su **total de asistencias acumuladas** (evidencia del concepto).

## Estado
🟢 **Implementada** — `Participant hasMany Attendance`; índice único `event_id + participant_id`
en `attendances` (anti-duplicado atómico, ver [[registro-de-asistencia]]); contador
`totalAttendances` en `AttendanceRegistration`.

## Notas técnicas
- El anti-duplicado es **atómico**: transacción + `lockForUpdate` + índice único de BD que atrapa
  carreras concurrentes. Ver [[adr-0003-retirar-flujo-legacy-de-asistencia]] y [[modelo-de-datos]].
- Las estadísticas demográficas se apoyan en el snapshot `attendance_details`
  ([[adr-0002-snapshot-demografico-attendance-details]]).

## Pruebas relacionadas
Cubierto en parte por las pruebas del registro de asistencia (duplicados). Ver
[[estrategia-de-pruebas]].
