# CLAUDE.md — Asistencia Uniguajira

Sistema web para gestión y control de asistencias en la Universidad de La Guajira.

**Stack:** Laravel 12 (PHP 8.2+) · Livewire 3 (Volt + Flux) + Blade + Tailwind CSS v4 ·
React 19 + Recharts (islas de estadísticas) · Vite 7 · MySQL (tests en SQLite `:memory:`) ·
FPDI + TFPDF (PDF) · simple-qrcode · maatwebsite/excel · Sanctum · laravel-lang (es / es_CO).

## Instrucciones por carpeta

Además de este archivo, hay CLAUDE.md con convenciones propias en:

| Carpeta | Cubre |
|---|---|
| `resources/js/` | Islas React (estadísticas, participantes, eventos), tema, puente con Livewire |
| `resources/views/administration/` | Patrón de 2 pestañas, importación/exportación Excel |

## Comandos de desarrollo

```bash
# Iniciar todos los servicios juntos (servidor, queue y vite)
composer run dev

# Solo frontend
npm run dev

# Solo backend
php artisan serve

# Compilar para producción
npm run build

# Tests
composer run test
# o
php artisan test

# Migraciones con seeders
php artisan migrate --seed

# Linter PHP (NO ejecutar de forma automática — ver regla abajo)
./vendor/bin/pint
```

## Regla de edición: NADA de cambios solo de formato

**Prohibido hacer cambios que sean únicamente de formato/estilo.** Cambia **solo** las líneas
necesarias para la tarea y **respeta el estilo del código que ya existe alrededor**.

Concretamente, **NO**:
- Ejecutar `./vendor/bin/pint` (ni ningún formateador) sobre archivos como parte de una tarea; toca
  líneas ajenas al cambio, no aporta valor y gasta tokens.
- Reformatear líneas que no estás modificando (espacios, `new Clase()` ↔ `new Clase`, espaciado de
  concatenaciones `.`, comillas, orden de `use`, saltos de línea, etc.).
- "Aprovechar" para limpiar estilo de código vecino.

Si un archivo ya tiene un estilo (aunque no sea el de Pint), **mantenlo**. El formateo global, si
se quiere, es una tarea aparte y explícita del equipo, no un efecto colateral de otro cambio.

## Roles y visibilidad de datos (aplica en todo el código)

- Tres niveles: `superadmin`, `admin` y usuario normal (constantes `User::ROLE_*`).
- `RoleMiddleware` (`role:admin,...`): pedir `admin` **incluye automáticamente** a `superadmin`
  (se auto-expande); solo `role:superadmin` excluye a los admin.
- Solo superadmin: campuses, formatos PDF, registros de actividad, exportar participantes.
- Visibilidad de eventos para usuarios normales: eventos propios (`user_id`) **o** de sus
  dependencias (`User` ↔ `Dependency` es **belongsToMany**, no una sola dependencia), más el
  alcance por sede vía `CampusScopeService`. Toda consulta nueva de eventos/estadísticas debe
  aplicar este alcance — no basta filtrar por `user_id`.
- `SetLocale` aplica el locale de sesión en cada request.

## Mapa de rutas

| Grupo | Prefijo | Acceso |
|---|---|---|
| Dashboard | `/dashboard` | auth + verified |
| Eventos (CRUD) | `/eventos/*` | auth + verified |
| Acceso público al evento (QR) | `/events/acceso/{slug}` | público + `throttle:public` |
| Estadísticas (subpáginas por tema) | `/estadisticas/*` | auth + verified (`usuarios` solo admin) |
| API de estadísticas y participantes | `/api/statistics/*`, `/api/participants` | `web + auth` (+ throttle / rol) |
| Gestión de usuarios | `/usuarios/*` | auth + verified + `role:admin,superadmin` |
| Administración del sistema | `/administracion/*` | auth + verified + `role:admin,superadmin` |
| Configuración de cuenta | `/settings/*` | auth |

El registro de asistencia por QR lo gestiona el componente Livewire `AttendanceRegistration`
montado en `events/access.blade.php` (el flujo por controlador se retiró — ADR-0003).

## Convenciones del proyecto

- **Idioma de código, rutas y UI:** español (locale `es`).
- **Livewire Volt:** componentes de clase en `app/Livewire/`, vistas en `resources/views/livewire/`.
- **PDF de asistencias:** FPDI/TFPDF en `EventController::descargarAsistencia()`; los formatos y
  sus coordenadas de mapeo viven en base de datos (`Format`, `FormatFile` — ADR-15/17).
- **Slugs de eventos:** usados para las URLs públicas de registro de asistencia.
- **Ramas:** `develop` es la rama base de integración; el trabajo va en ramas `feat/*`, `fix/*`,
  `refactor/*` que salen de develop.
- **Vault AURA:** `vault/AURA/` es la documentación viva del proyecto (Obsidian: ADRs, diseño,
  tablero); al cambiar arquitectura o cerrar funcionalidades, actualizarla.

## Variables de entorno relevantes

```
APP_NAME='Asistencia Uniguajira'
APP_LOCALE=es
APP_FAKER_LOCALE=es_CO
DB_CONNECTION=mysql         # ver .env.example; los tests usan SQLite :memory: (phpunit.xml)
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## Notas importantes

- **La aplicación ya está en producción** (Hostinger, hosting compartido — sin procesos
  residentes). Consecuencia: las migraciones deben ser **aditivas**; jamás `migrate:fresh`,
  `migrate:refresh`, `db:wipe` ni seeders destructivos sobre una base con datos reales.
- Los participantes se registran públicamente vía QR (sin necesidad de cuenta).
- La importación masiva de participantes es asíncrona (queue) con pasarela de revisión
  (`ImportBatch` / `StagedParticipant` — ADR-0004).
