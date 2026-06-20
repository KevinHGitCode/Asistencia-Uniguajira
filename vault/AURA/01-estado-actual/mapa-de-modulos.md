---
tipo: estado-actual
descripcion: Inventario de módulos del sistema con su grado real de implementación
actualizado: 2026-06-20
---

# Mapa de módulos

Grado de implementación verificado en controladores, componentes Livewire, rutas y vistas.
Leyenda: ✅ funcional · 🟡 parcial / con deuda · 🧟 legacy (vivo pero sin uso).

| Módulo | Estado | Dónde vive | Notas |
|---|---|---|---|
| Autenticación | ✅ | starter kit Livewire, `routes/auth.php` | Registro, verificación, reset |
| Dashboard + calendario | ✅ | `DashboardController`, `api.php` (`eventos-json`, `mis-eventos-json`) | Indicador "Hoy" con CSS |
| Eventos (CRUD) | ✅ | `EventController`, `CreateEventWizard` (Livewire), `EventService` | `link` único para QR; terminar manual (`ended_at`) |
| **Registro público de asistencia (QR)** | ✅ | `AttendanceRegistration` (Livewire), vista `events/access` | Flujo rico — ver [[registro-de-asistencia]] |
| Registro de asistencia (controlador) | 🧟 | `AttendanceController::store` + `confirmation`, vista `events/confirmation`, ruta `attendance.store` | **Legacy**: la vista de acceso ya monta el componente Livewire — ver [[brechas-conocidas]] |
| PDF de asistencia | ✅ | `EventController::descargarAsistencia`, `AttendancePdfService`, FPDI | Formato `general` + formatos por dependencia con mapper |
| Formatos (plantillas PDF + mapper) | ✅ | `FormatController`, `config/attendance_formats.php` | Valida compatibilidad FPDI; escribe config con lock |
| Administración | ✅ | `app/Http/Controllers/Configuration/*`, `app/Livewire/Administration/*` | Patrón 2 pestañas + Excel + paginación |
| └ Dependencias / Áreas | ✅ | `DependencyController`, `AreaController` | Import/export/plantilla |
| └ Programas / Afiliaciones / Estamentos | ✅ | `Program/Affiliation/ParticipantTypeController` | Import/export/plantilla |
| └ Organizaciones | ✅ | `OrganizationController` | Búsqueda, merge, import |
| └ Importación de participantes | ✅ | `ParticipantImportController` | Plantilla + reporte de descartados |
| └ Registros de actividad (auditoría) | ✅ | `ActivityLogController`, `ActivityLogService` | Limpiar logs |
| Estadísticas | ✅ | `StatisticsController` + `routes/api.php`, React en `resources/js/statistics/` | Resumen, top, por rol/sexo/grupo/dependencia/organización, comparar eventos |
| Usuarios (admin) | ✅ | `UserController`, `app/Livewire/User/*` | CRUD + toggle activo |
| Settings | ✅ | `app/Livewire/Settings/*` | Perfil, password, apariencia, idioma, about |
| Correo de confirmación | ✅ | `AttendanceRegisteredMail` | Se envía al registrar (si hay email) |

## Permisos (resumen)
- `admin` — acceso completo; rutas `usuarios/*`, `administracion/*`, `estadisticas/usuarios`
  con middleware `role:admin`.
- Usuario normal — ve sus eventos y los de **sus dependencias** (`belongsToMany`).
- Participante — sin cuenta; registra asistencia por el QR público.

Detalle de personas en [[personas-y-roles]]. Inconsistencias en [[brechas-conocidas]].
