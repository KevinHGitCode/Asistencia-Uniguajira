---
tipo: diseno
descripcion: Cómo encajan las piezas — Laravel + Livewire + React islands + servicios
actualizado: 2026-06-20
---

# Arquitectura

Monolito Laravel 12 con UI híbrida. Stack exacto en [[stack-tecnologico]].

## Capas
- **Rutas**: `routes/web.php` (UI con sesión), `routes/api.php` (JSON: calendario,
  estadísticas, datos de prueba), `routes/auth.php` (starter kit).
- **Controladores**: tradicionales en `app/Http/Controllers/` (eventos, asistencia, usuarios,
  estadísticas) y `Configuration/` (administración).
- **Componentes Livewire** (`app/Livewire/`): formularios y flujos interactivos con estado de
  servidor (wizard de eventos, registro de asistencia, tablas de administración, settings).
- **Servicios** (`app/Services/`): lógica reutilizable —
  `EventService`, `AttendancePdfService`, `StatisticsService`, `ActivityLogService`.
- **Traits** (`app/Traits/`): p.ej. `AppliesStatisticsFilters` (filtros compartidos).
- **Modelos Eloquent** (`app/Models/`): ver [[modelo-de-datos]].

## UI híbrida (3 estilos coexisten)
1. **Blade + Flux** — vistas y layout general.
2. **Alpine.js** — interactividad ligera (búsqueda en tablas, toggles).
3. **React Islands** — solo estadísticas, montado en `#statistics-react-root`
   (`resources/js/statistics/`), con Recharts. Soporta `wire:navigate` re-montando en
   `livewire:navigated`. Decisión: [[adr-0001-react-islands-estadisticas]].

## Flujos transversales
- **Auth/roles**: middleware `RoleMiddleware` (`role:admin`) y `SetLocale`.
- **Auditoría**: `ActivityLogService::log(...)` escribe en `activity_logs` desde varios módulos.
- **PDF**: `AttendancePdfService` + FPDI sobre plantillas de `Format`; mapeo en
  `config/attendance_formats.php` (escrito con file-lock por `FormatController`).
- **Estadísticas**: el front llama a `/api/statistics/*`; los `*-summary` filtran por rol del
  usuario; los individuales hoy van sin auth (ver [[brechas-conocidas]] #4).

## Decisiones de arquitectura registradas
- [[adr-0001-react-islands-estadisticas]] 🟢
- [[adr-0002-snapshot-demografico-attendance-details]] 🟢
- [[adr-0003-retirar-flujo-legacy-de-asistencia]] 🟡
