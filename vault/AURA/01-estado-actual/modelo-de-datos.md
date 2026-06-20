---
tipo: estado-actual
descripcion: Modelo de datos real derivado de las 27 migraciones y los modelos Eloquent
actualizado: 2026-06-20
---

# Modelo de datos

Derivado de `database/migrations/` (27 migraciones) y `app/Models/`. Más rico que la tabla
de `CLAUDE.md`, que está incompleta (ver [[brechas-conocidas]]).

## Tablas y relaciones

### Núcleo organizativo
- **users** — `role` (string: `admin` / normal), `is_active`, `avatar`.
  `User belongsToMany Dependency` vía **dependency_user** (⚠️ no `belongsTo`, como dice CLAUDE.md).
- **dependencies** — `hasMany areas`, `belongsToMany formats` (pivot `dependency_format`).
- **areas** — pertenecen a una dependencia (opcional en eventos).
- **dependency_user** — pivot usuario↔dependencia (migración 2025_11_15).

### Eventos y asistencia
- **events** — `title`, `description`, `date`, `start_time`, `end_time`, `ended_at`,
  `location`, **`link`** (string único, 500; slug público del QR), `user_id` (dueño),
  `dependency_id` (nullable, `set null`), `area_id` (nullable).
- **participants** — `document` (único), `student_code` (único, nullable),
  `first_name`, `last_name`, `email` (único, nullable). **Sin** datos demográficos: esos
  viven en el detalle de cada asistencia.
- **attendances** — `event_id` + `participant_id`, **único (event, participant)**,
  borrado de participante **restringido** si tiene asistencias.
- **attendance_details** — *snapshot por asistencia*: `participant_role_id`, `gender`,
  `phone`, `city`, `neighborhood`, `address`, `priority_group`. Captura el dato **en el
  momento del registro** (ver [[adr-0002-snapshot-demografico-attendance-details]]).

### Identidad del participante (la pieza clave)
- **participant_roles** — un participante puede tener **varios roles activos**. Cada rol =
  `participant_type_id` (estamento) + `program_id?` + `dependency_id?` + `affiliation_id?`
  + `organization_id?` + `is_active`. Índices para filtrar por activo.
- **participant_types** — estamentos (Estudiante, Docente, *Comunidad Externa*, …).
- **programs** — programas académicos.
- **affiliations** — afiliaciones.
- **organizations** — entidades externas (comunidad externa), autocompletado en el registro.

### Formatos de PDF
- **formats** — `name`, `slug` (único), `file` (plantilla PDF), `mapping` (JSON).
  Pivot **dependency_format**. El mapeo se edita en un *mapper visual* y se persiste también
  en `config/attendance_formats.php`.

### Auditoría y soporte
- **activity_logs** — auditoría: `user_id?`, `participant_id?`, `action`, `module`,
  `description`, `subject_type/id`, `ip_address`, `user_agent`, `metadata` (JSON).
- **personal_access_tokens** (Sanctum), **cache**, **jobs**.
- Migración `2026_03_07` añade índices de rendimiento para estadísticas.

## Diagrama mental (texto)

```
User ─┬─< Event >─┬─ Dependency ─< Area
      │           └─< Attendance >── Participant ─< ParticipantRole ─┬─ ParticipantType
      └── dependency_user ── Dependency             │                 ├─ Program
                                                    │                 ├─ Dependency
Attendance ──1:1── AttendanceDetail ── ParticipantRole                ├─ Affiliation
Dependency ─< dependency_format >─ Format                             └─ Organization
```

## Relacionado
- Cómo se usan estas tablas en el flujo público → [[registro-de-asistencia]]
- Estadísticas que consumen `attendance_details` → [[mapa-de-modulos]]
