# CLAUDE.md — Asistencia Uniguajira

Sistema web para gestión y control de asistencias en la Universidad de La Guajira.

## Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Livewire (Volt + Flux), Blade, Tailwind CSS v4
- **Base de datos:** SQLite (local), configurable a MySQL/Postgres via `.env`
- **Build:** Vite 7, NPM
- **PDF:** setasign/FPDI + TFPDF
- **QR:** simplesoftwareio/simple-qrcode
- **Exportación:** maatwebsite/excel
- **Auth:** Laravel Sanctum + Livewire starter kit
- **Internacionalización:** laravel-lang (es / es_CO por defecto)

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

# Linter PHP
./vendor/bin/pint
```

## Arquitectura

### Modelos y relaciones clave

| Modelo | Tabla | Relaciones principales |
|---|---|---|
| `User` | `users` | belongsTo `Dependency`, hasMany `Event` |
| `Dependency` | `dependencies` | hasMany `User`, hasMany `Event` |
| `Event` | `events` | belongsTo `User`, belongsTo `Dependency`, hasMany `Attendance`, belongsToMany `Participant` |
| `Participant` | `participants` | hasMany `Attendance`, belongsToMany `Event` |
| `Attendance` | `attendances` | belongsTo `Event`, belongsTo `Participant` |
| `Program` | `programs` | hasMany `Participant` |

### Roles de usuario

- `admin` — acceso completo, gestión de usuarios (`/usuarios/*` con middleware `role:admin`)
- Usuarios normales — ven sus propios eventos y los de su dependencia

### Middleware personalizado

- `RoleMiddleware` — restringe rutas por rol (`role:admin`)
- `SetLocale` — aplica el locale de sesión en cada request

## Estructura de rutas (`routes/web.php`)

| Grupo | Prefijo | Acceso |
|---|---|---|
| Dashboard | `/dashboard` | auth + verified |
| Eventos (CRUD) | `/eventos/*` | auth + verified |
| Acceso público al evento (QR) | `/events/acceso/{slug}` | público |
| Confirmación de asistencia | `/events/acceso/{slug}/confirmacion/{id}` | público |
| Estadísticas | `/estadisticas`, `/graficos/tipos` | auth + verified |
| Gestión de usuarios | `/usuarios/*` | auth + verified + `role:admin` |
| Configuración | `/settings/*` | auth |

## Convenciones del proyecto

- **Idioma de código y rutas:** español (variables, vistas, rutas nombradas)
- **Idioma de la UI:** español (locale `es`)
- **Livewire Volt:** componentes de clase en `app/Livewire/`, vistas en `resources/views/livewire/`
- **PDF de asistencias:** generado con FPDI/TFPDF en `EventController::descargarAsistencia()`
- **Eventos:** filtrado por `user_id` (propios) y `dependency_id` (de la dependencia del usuario)
- **Slugs de eventos:** usados para URLs públicas de registro de asistencia
- **Branch:** trabajar siempre en develop

## Directorios importantes

```
app/
  Http/Controllers/     # Controladores tradicionales
  Livewire/             # Componentes Livewire (Volt, class-based)
  Models/               # Modelos Eloquent
  Traits/               # Traits reutilizables (AppliesStatisticsFilters, etc.)
database/
  migrations/           # Migraciones en orden cronológico
  seeders/              # Seeders de datos de prueba
resources/views/
  events/               # Vistas de eventos
  users/                # Vistas de usuarios
  statistics/           # Vistas de estadísticas y gráficos
  calendar/             # Vista de calendario
  livewire/             # Componentes Blade de Livewire
routes/
  web.php               # Rutas principales
  auth.php              # Rutas de autenticación (generadas por starter kit)
```

## Variables de entorno relevantes

```
APP_NAME='Asistencia Uniguajira'
APP_LOCALE=es
APP_FAKER_LOCALE=es_CO
DB_CONNECTION=sqlite        # SQLite en local
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## Notas importantes

- La base de datos SQLite local está en `database/database.sqlite`
- El despliegue en producción es via Render (`https://asistencia-uniguajira.onrender.com/`)
- Los reportes PDF de asistencia incluyen: info del evento, dependencia y lista de participantes
- Los participantes se registran públicamente vía QR (sin necesidad de cuenta)
- El calendario utiliza animaciones CSS personalizadas para el indicador "Hoy"
