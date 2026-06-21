---
tipo: estado-actual
descripcion: Seguimiento vivo de la migracion multi-sede y su implementacion por modulo
actualizado: 2026-06-20
---

# Migracion multi-sede

Esta nota debe revisarse constantemente mientras avance la migracion multi-sede. Su funcion es
responder rapido: **este modulo ya respeta sede o todavia mezcla datos?**

## Regla de negocio objetivo
- Sedes iniciales: **Maicao**, **Riohacha**, **Fonseca**, **Villanueva**.
- Roles: `user`, `admin`, `superadmin`.
- `admin` y `user` pertenecen a una sede (`campus_id` obligatorio cuando se complete la migracion).
- `superadmin` tiene `campus_id = null`.
- `superadmin` sin sede activa ve todas las sedes.
- `superadmin` con sede activa ve solo esa sede.
- `participants` sigue siendo global.
- `formats` sigue siendo global.
- No usar global scopes automaticos mientras existan rutas publicas y modulos sin migrar.

## Estado estructural implementado
- Tabla **campuses** creada con `name` unico.
- Modelo **Campus** creado.
- Seeder idempotente de sedes iniciales creado.
- `campus_id` nullable agregado a:
  - `users`
  - `events`
  - `dependencies`
  - `programs`
  - `areas`
- Relaciones Eloquent agregadas:
  - `User/Event/Dependency/Program/Area belongsTo Campus`.
  - `Campus hasMany users/events/dependencies/programs/areas`.
- Tabla **academic_programs** creada.
- `programs.academic_program_id` nullable agregado.
- `Program belongsTo AcademicProgram`.
- `AcademicProgram hasMany Program`.

## Backfills implementados
- Backfill seguro de `campus_id` por inferencia de nombre y fallback **Maicao**.
- Backfill de `academic_programs` desde `programs.name`, quitando sufijos finales:
  - ` - Maicao`
  - ` - Riohacha`
  - ` - Fonseca`
  - ` - Villanueva`
- Ambos procesos deben ser idempotentes y reportar conteos.

## Servicios y soporte transversal
- `CampusScopeService` centraliza:
  - sede activa,
  - deteccion de superadmin,
  - filtro de queries por `campus_id`,
  - validacion de acceso a recursos por sede.
- La sede activa del superadmin se guarda en sesion con `CampusScopeService::SESSION_KEY`.
- No se aplican global scopes.

## Matriz de implementacion por modulo

Leyenda: ✅ implementado · 🟡 parcial · ⬜ pendiente · 🚫 no aplica / global.

| Modulo | Estado sede | Evidencia / notas |
|---|---:|---|
| Dashboard | ✅ | `DashboardController` aplica `CampusScopeService` a conteos y dependencias. Superadmin tiene selector de sede activa. |
| Calendario dashboard | ✅ | `/api/eventos-json`, `/api/events/{date}` y `/api/mis-eventos-json` pasan por auth web y filtran por sede. |
| Eventos privados (listado, crear, detalle, editar, eliminar, terminar) | ✅ | `EventController`, `EventService`, `CreateEventWizard` y `EditEventModal` aplican sede. Admin gestiona solo su sede; superadmin respeta sede activa o ve todo sin seleccion. |
| Detalle de evento desde calendario | ✅ | `EventController::show` valida sede antes de permitir ver detalle. |
| Ruta publica QR `/events/acceso/{slug}` | 🚫 | Debe seguir publica y sin filtro de sede. No romper. |
| Confirmacion publica de asistencia | 🚫 | Debe seguir publica. Revisar solo seguridad anti-abuso, no sede. |
| Estadisticas | ✅ | `StatisticsController` resuelve sede activa con `CampusScopeService`; `StatisticsService` filtra bases por `events.campus_id`. Endpoints `/api/statistics/*` requieren sesion. El selector de sede de superadmin actualiza graficas por AJAX sin recargar toda la pagina. |
| Comparador de eventos | ✅ | `/api/statistics/compare/events` y `/api/statistics/compare/data` aplican visibilidad por sede/usuario antes de listar o comparar eventos. |
| Descarga privada de PDF de evento | ✅ | No cambia generacion PDF; se agrego autorizacion por sede para evitar bypass por URL directa. |
| Wizard de creacion de evento | ✅ | Limita dependencias/areas por sede y asigna `events.campus_id`. |
| Administracion dependencias | ⬜ | Listados, CRUD, import/export deben filtrar o asignar sede. |
| Administracion areas | ⬜ | Listados, CRUD, import/export deben filtrar o asignar sede. |
| Administracion programas | ⬜ | Listados, CRUD, import/export deben filtrar o asignar sede y respetar `academic_program_id`. |
| Administracion afiliaciones | 🚫 | Global por ahora, sin `campus_id`. |
| Administracion estamentos | 🚫 | Global por ahora, sin `campus_id`. |
| Administracion organizaciones | 🚫 | Global por ahora, sin `campus_id`. |
| Importacion de participantes | 🚫 | `participants` global; revisar roles asociados sin filtrar participantes directamente. |
| Usuarios | 🟡 | Roles `user/admin/superadmin` y reglas base ya existen; revisar listados/edicion por sede en cada flujo. |
| Formatos PDF | 🚫 | Global; no filtrar directamente. La asociacion por dependencia puede heredar sede indirectamente. |
| Activity logs | ⬜ | Revisar si se requiere filtro por sede o solo auditoria global para superadmin. |

## Checklist obligatorio por modulo
- [ ] Identificar si el modulo maneja datos con `campus_id`.
- [ ] Confirmar si debe usar `CampusScopeService`.
- [ ] Confirmar que `superadmin` sin sede no filtra.
- [ ] Confirmar que `superadmin` con sede filtra.
- [ ] Confirmar que `admin/user` no puedan consultar ni mutar otra sede.
- [ ] Confirmar que no se filtren `participants` ni `formats` directamente.
- [ ] Confirmar que rutas publicas por QR no se rompan.
- [ ] Agregar pruebas del modulo antes de marcarlo como ✅.

## Riesgos activos
- Imports y administracion pueden mezclar sedes si se filtran parcialmente.
- Estadisticas ya filtra por sede desde eventos/asistencias; mantener vigilancia al agregar nuevas graficas para no consultar `participants` globalmente sin pasar por eventos.
- Crear eventos sin asignar `campus_id` deja datos invisibles para admin/user una vez se endurezcan reglas.
- `campus_id` sigue nullable para migracion progresiva; no volver `NOT NULL` hasta completar auditoria y backfill.
- Tests feature requieren driver SQLite local; si falta, las pruebas con DB fallan antes de aserciones.

## Relacionado
[[modelo-de-datos]] · [[mapa-de-modulos]] · [[arquitectura]] · [[adr-0009-migracion-multi-sede-progresiva]]
