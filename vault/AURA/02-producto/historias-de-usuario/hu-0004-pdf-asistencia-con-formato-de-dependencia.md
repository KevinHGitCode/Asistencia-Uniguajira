---
tipo: historia-usuario
descripcion: El responsable descarga el acta de asistencia con el formato oficial de su dependencia
actualizado: 2026-06-25
---

# HU-0004 · Descargar el acta de asistencia con el formato de mi dependencia

**Como** usuario de dependencia (o admin) responsable de un evento,
**quiero** descargar el **PDF de asistencia con el formato oficial de mi dependencia**,
**para** entregar la evidencia formal sin armarla a mano.

## Contexto / valor
Cada dependencia tiene su **formato oficial** de acta. La app **rellena ese formato** con la info
del evento, la dependencia y la lista de asistentes, y entrega el PDF listo para firmar/archivar.
Resuelve la necesidad de "evidencia formal" de la [[vision]] sin trabajo manual ni reformateos.

## Criterios de aceptación
- [x] Desde el detalle del evento puedo **descargar** el acta en PDF.
- [x] El PDF usa el **formato de la dependencia** del evento; si no tiene uno asignado, usa el
      formato **general**.
- [x] El PDF incluye **datos del evento, dependencia y lista de participantes**.
- [x] Solo puede descargar quien tiene permiso (admin, dueño del evento o miembro de la dependencia);
      en otro caso, **403**.
- [x] La descarga queda registrada en **auditoría**.

## Estado
🟢 **Implementada** — `EventController::descargarAsistencia` + `AttendancePdfService` (FPDI sobre la
plantilla del formato). Flujo detallado en [[cu-0001-descargar-pdf-asistencia]].

## Notas técnicas
- El mapeo de campos del formato (dónde va cada dato en el PDF) se administra con el *mapper* visual;
  ver [[adr-0015-mapeo-de-formatos-fuente-de-verdad-en-bd]].
- Plantillas PDF deben ser compatibles con FPDI (versión ≤ 1.4 / sin compresión no soportada).

## Pruebas relacionadas
`tests/Feature/Event/AttendancePdfCampusCompatibilityTest.php` (compatibilidad de formato por sede).
