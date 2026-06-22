---
tipo: diseno
descripcion: Como encajan las piezas - Laravel + Livewire + React islands + servicios
actualizado: 2026-06-20
---

# Arquitectura

Monolito Laravel 12 con UI hibrida. Stack exacto en [[stack-tecnologico]].

## Capas
- **Rutas**: `routes/web.php` (UI con sesion), `routes/api.php` (JSON: calendario,
  estadisticas, datos auxiliares), `routes/auth.php` (starter kit).
- **Controladores**: tradicionales en `app/Http/Controllers/` y `Configuration/`.
- **Componentes Livewire** (`app/Livewire/`): formularios y flujos con estado de servidor.
- **Servicios** (`app/Services/`): logica reutilizable:
  `EventService`, `AttendancePdfService`, `StatisticsService`, `ActivityLogService`,
  `CampusScopeService`.
- **Traits** (`app/Traits/`): filtros compartidos, por ejemplo `AppliesStatisticsFilters`.
- **Modelos Eloquent** (`app/Models/`): ver [[modelo-de-datos]].

## UI hibrida
1. **Blade + Flux** - vistas y layout general.
2. **Alpine.js** - interactividad ligera.
3. **React Islands** - estadisticas en `resources/js/statistics/`, con Recharts.

## Multi-sede
- La estrategia vigente es progresiva y sin global scopes automaticos.
- `CampusScopeService` decide la sede activa y aplica filtros por `campus_id`.
- `superadmin` usa sede activa en sesion; sin seleccion ve todas.
- `participants` y `formats` son globales y no se filtran directamente.
- Dashboard y calendario ya usan esta capa.
- El seguimiento modulo por modulo vive en [[migracion-multi-sede]].
- Decision registrada: [[adr-0009-migracion-multi-sede-progresiva]].

## Flujos transversales
- **Auth/roles**: middleware `RoleMiddleware`; roles objetivo `user`, `admin`, `superadmin`.
- **Auditoria**: `ActivityLogService::log(...)` escribe en `activity_logs`.
- **PDF**: `AttendancePdfService` + FPDI sobre plantillas de `Format`.
- **Estadisticas**: React consume `/api/statistics/*`; pendiente migracion completa a sede.

## Decisiones de arquitectura registradas
- [[adr-0001-react-islands-estadisticas]] 🟢
- [[adr-0002-snapshot-demografico-attendance-details]] 🟢
- [[adr-0003-retirar-flujo-legacy-de-asistencia]] 🟡
- [[adr-0004-pasarela-de-revision-para-importacion-de-participantes]] 🟢
- [[adr-0005-rate-limiting-anti-abuso]] 🟢
- [[adr-0006-formularios-en-modal-centrado]] 🟡
- [[adr-0007-paleta-de-comandos-admin]] 🟡
- [[adr-0008-listado-participantes-en-react]] 🟡
- [[adr-0009-migracion-multi-sede-progresiva]] 🟢
- [[adr-0010-mejoras-modulo-usuarios]] 🟡
- [[adr-0011-mejores-filtros-en-participantes]] 🟡
- [[adr-0012-busqueda-y-filtros-en-eventos]] 🟡
- [[adr-0013-breadcrumbs-detalle-evento]] 🟢
- [[adr-0014-proteger-rutas-api-de-prueba-sin-auth]] 🟡
