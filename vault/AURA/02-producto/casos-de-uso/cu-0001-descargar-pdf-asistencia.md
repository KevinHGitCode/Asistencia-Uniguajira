---
tipo: caso-uso
descripcion: CU sembrado desde el repo — descargar el PDF de asistencia de un evento
actualizado: 2026-06-20
---

# CU-0001 · Descargar PDF de asistencia

> Ejemplo sembrado desde `EventController::descargarAsistencia`. Plantilla: [[plantilla-caso-de-uso]].

## Actor
[[personas-y-roles|Usuario de dependencia]] o **admin** (dueño del evento, miembro de la
dependencia, o admin).

## Precondiciones
- El evento existe y tiene asistencias.
- El actor tiene permiso sobre el evento (admin / dueño / pertenece a la dependencia).

## Flujo principal
1. El actor abre el detalle del evento (`GET /eventos/{id}`).
2. Solicita la descarga: `GET /eventos/{id}/descargar-asistencia/{formatSlug?}`.
3. El sistema resuelve el **formato**: si la dependencia no tiene formatos asignados o no se
   pasa slug, usa `general`.
4. `AttendancePdfService` genera el PDF con FPDI sobre la plantilla del formato.
5. Se registra la acción en auditoría (`exportar`, módulo `eventos`).
6. El sistema responde con el PDF (`Content-Disposition: attachment`).

## Flujos alternativos / errores
- **Sin permiso** → `403`.
- **Dependencia sin acceso al formato** pedido → `403`.
- **Plantilla PDF incompatible** (versión > 1.4 / compresión no soportada por FPDI) → vuelve
  con mensaje pidiendo re-subir la plantilla en versión compatible.

## Postcondiciones
- Se entrega un PDF nombrado `Asistencia_{titulo}_{fecha}.pdf`.
- Queda traza en `activity_logs`.

## Relacionado
- Formatos y mapper → [[mapa-de-modulos]] · Datos → [[modelo-de-datos]]
