---
tipo: estado-actual
descripcion: Modelo de datos real derivado de migraciones y modelos Eloquent
actualizado: 2026-06-20
---

# Modelo de datos

Derivado de `database/migrations/` y `app/Models/`. Mas rico que la tabla de `CLAUDE.md`,
que esta incompleta (ver [[brechas-conocidas]]).

## Tablas y relaciones

### Nucleo organizativo
- **campuses** - sedes iniciales: Maicao, Riohacha, Fonseca y Villanueva. Es la base de la
  migracion multi-sede progresiva. Estado vivo en [[migracion-multi-sede]].
- **users** - `role` (`user` / `admin` / `superadmin`), `is_active`, `avatar`, `campus_id`
  nullable durante migracion. `superadmin` debe tener `campus_id = null`; `admin` y `user`
  pertenecen a una sede.
- **dependencies** - `campus_id` nullable, `hasMany areas`, `belongsToMany formats`
  (pivot `dependency_format`).
- **areas** - `campus_id` nullable, pertenecen a una dependencia (opcional en eventos).
- **dependency_user** - pivot usuario-dependencia. El codigo real es
  `User belongsToMany Dependency`, no `belongsTo`.

### Eventos y asistencia
- **events** - `title`, `description`, `date`, `start_time`, `end_time`, `ended_at`,
  `location`, **`link`** (slug publico del QR), `user_id` (dueno), `dependency_id`
  nullable, `area_id` nullable, `campus_id` nullable durante migracion.
- **participants** - global. `document` unico, `student_code` unico nullable,
  `first_name`, `last_name`, `email` unico nullable. No filtrar directamente por sede.
- **attendances** - `event_id` + `participant_id`, unico por evento/participante.
- **attendance_details** - snapshot por asistencia: `participant_role_id`, `gender`,
  `phone`, `city`, `neighborhood`, `address`, `priority_group`.

### Identidad del participante
- **participant_roles** - un participante puede tener varios roles activos. Cada rol =
  `participant_type_id` + `program_id?` + `dependency_id?` + `affiliation_id?` +
  `organization_id?` + `is_active`.
- **participant_types** - estamentos.
- **programs** - instancias por sede/importacion; `campus_id` nullable y
  `academic_program_id` nullable durante migracion.
- **academic_programs** - catalogo global del programa academico normalizado. Se pobla desde
  `programs.name` quitando sufijos de sede (` - Maicao`, ` - Riohacha`, ` - Fonseca`,
  ` - Villanueva`) sin modificar `programs.name`.
- **affiliations** - global.
- **organizations** - global, usado para comunidad externa.

### Formatos de PDF
- **formats** - global. `name`, `slug` unico, `file` (plantilla PDF), `mapping` JSON.
  Pivot **dependency_format**. No filtrar `formats` directamente por sede.

### Auditoria y soporte
- **activity_logs** - auditoria: `user_id?`, `participant_id?`, `action`, `module`,
  `description`, `subject_type/id`, `ip_address`, `user_agent`, `metadata` JSON.
- **personal_access_tokens** (Sanctum), **cache**, **jobs**.

## Diagrama mental

```text
Campus -< User -< Event >- Dependency -< Area
   |       |          |
   |       +-- dependency_user -- Dependency
   |
   +--< Dependency
   +--< Area
   +--< Program >-- AcademicProgram

Event -< Attendance >-- Participant -< ParticipantRole >-- ParticipantType
                                      |                  +-- Program
                                      |                  +-- Dependency
                                      |                  +-- Affiliation
                                      |                  +-- Organization
Attendance --1:1-- AttendanceDetail -- ParticipantRole
Dependency -< dependency_format >- Format
```

## Reglas multi-sede importantes
- `participants` y `formats` siguen globales.
- `campus_id` sigue nullable hasta terminar backfill/auditoria.
- No usar global scopes automaticos por ahora.
- Aplicar `CampusScopeService` modulo por modulo.

## Relacionado
- Como se usan estas tablas en el flujo publico -> [[registro-de-asistencia]]
- Estadisticas que consumen `attendance_details` -> [[mapa-de-modulos]]
- Seguimiento multi-sede por modulo -> [[migracion-multi-sede]]
