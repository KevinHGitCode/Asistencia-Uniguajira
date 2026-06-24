---
tipo: estado-actual
descripcion: Inventario de modulos del sistema con su grado real de implementacion
actualizado: 2026-06-20
---

# Mapa de modulos

Grado de implementacion verificado en controladores, componentes Livewire, rutas y vistas.
Leyenda: ✅ funcional · 🟡 parcial / con deuda · 🧟 legacy vivo pero sin uso.

> Seguimiento multi-sede: revisar siempre [[migracion-multi-sede]] antes de tocar un modulo
> que lea o escriba `users`, `events`, `dependencies`, `areas` o `programs`.

| Modulo | Estado | Donde vive | Notas |
|---|---|---|---|
| Autenticacion | ✅ | starter kit Livewire, `routes/auth.php` | Registro, verificacion, reset |
| Dashboard + calendario | ✅ | `DashboardController`, `routes/api.php` (`eventos-json`, `events/{date}`, `mis-eventos-json`) | Ya respeta sede con `CampusScopeService`; superadmin puede seleccionar sede activa |
| Eventos (CRUD) | ✅ | `EventController`, `CreateEventWizard`, `EditEventModal`, `EventService` | Listado, creacion, detalle privado, edicion, terminar y eliminacion ya respetan sede |
| Registro publico de asistencia (QR) | ✅ | `AttendanceRegistration` (Livewire), vista `events/access` | Flujo publico; no filtrar por sede directamente. Ver [[registro-de-asistencia]] |
| PDF de asistencia | ✅ | `EventController::descargarAsistencia`, `AttendancePdfService`, FPDI | Generacion sin cambios; descarga privada valida sede |
| Formatos (plantillas PDF + mapper) | ✅ | `FormatController`, `config/attendance_formats.php` | Global; no filtrar `formats` directamente |
| Administracion | 🟡 | `app/Http/Controllers/Configuration/*`, `app/Livewire/Administration/*` | Patron 2 pestanas + Excel + paginacion; dependencias y programas ya tienen sede, falta revisar entidades restantes |
| └ Dependencias / Areas | 🟡 | `DependencyController`, `AreaController` | Dependencias filtra/asigna/exporta/importa por sede. Areas queda fuera de la fase actual y sigue pendiente. |
| └ Programas / Afiliaciones / Estamentos | 🟡 | `Program/Affiliation/ParticipantTypeController` | Programas filtra/asigna/exporta/importa por sede y usa `academic_program_id`; afiliaciones y estamentos son globales |
| └ Organizaciones | ✅ | `OrganizationController` | Global por ahora |
| └ Importacion de participantes | ✅ | `ParticipantImportController` | Participantes globales; no filtrar participantes directamente |
| └ Registros de actividad | 🟡 | `ActivityLogController`, `ActivityLogService` | Pendiente decidir si auditoria se filtra por sede o queda global para superadmin |
| Estadisticas | ✅ | `StatisticsController`, `StatisticsService`, `routes/api.php`, React en `resources/js/statistics/` | Filtra por sede desde eventos/asistencias con `CampusScopeService`; no filtrar `participants` globalmente |
| Usuarios (admin) | 🟡 | `UserController`, `app/Livewire/User/*` | Roles `user/admin/superadmin` ya existen; revisar listados/edicion por sede en cada flujo |
| Settings | ✅ | `app/Livewire/Settings/*` | Perfil, password, apariencia, idioma, about |
| Correo de confirmacion | ✅ | `AttendanceRegisteredMail` | Se envia al registrar si hay email |

## Permisos actuales / objetivo multi-sede
- `superadmin` - acceso global; si selecciona sede activa, ve solo esa sede en modulos migrados.
- `admin` - debe ver y gestionar solo su sede.
- `user` - debe ver su sede y conservar reglas actuales de eventos propios/dependencias.
- Participante - sin cuenta; registra asistencia por QR publico.

Detalle de personas en [[personas-y-roles]]. Inconsistencias en [[brechas-conocidas]].
Seguimiento vivo de sedes en [[migracion-multi-sede]].
