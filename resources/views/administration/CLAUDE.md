# resources/views/administration — Módulo de administración

## Patrón de vistas: 2 pestañas

Las vistas de entidades (dependencias, áreas, programas, afiliaciones, estamentos, campuses,
organizaciones, participantes) siguen el patrón de **2 pestañas**:

| Pestaña | Contenido |
|---|---|
| **Listado** | Tabla de registros con búsqueda en tiempo real (Alpine.js) |
| **Importar / Exportar** | Drop zone para Excel + botón de descarga del listado actual |

El botón "Nuevo X" se mantiene en el header, no como pestaña. `formats/` y `logs/` son la
excepción: tienen su propia estructura.

Al crear una entidad administrable nueva, replicar el patrón completo (2 pestañas + import +
template + export), no solo el CRUD.

## Rutas — convención de nombres

Todas bajo el prefijo `/administracion` con `auth + verified + role:admin,superadmin`
(campuses, formatos y activity-logs exigen `role:superadmin`). Por entidad:

| Acción | Nombre de ruta |
|---|---|
| Importar Excel (POST) | `{entidad}.import` |
| Descargar plantilla vacía | `{entidad}.download-template` |
| Descargar listado actual | `{entidad}.download-export` |
| Descargar filas omitidas en la última importación | `{entidad}.download-skipped` (solo algunas) |

Los participantes usan el prefijo `participants-import.*` y además una **pasarela de revisión**
(ADR-0004): la importación es asíncrona por lotes (`ImportBatch`), con rutas
`participants-import.batches / .status / .review / .approve / .reject`.

## Clases de exportación (`app/Exports/`)

Una tripleta por entidad, con nombres predecibles:

- `{Entidad}Export` — listado actual.
- `{Entidad}TemplateExport` — plantilla vacía para carga masiva.
- `Skipped{Entidad}Export` — filas omitidas de la importación (solo entidades que lo soportan:
  dependencias, programas, organizaciones, participantes).
