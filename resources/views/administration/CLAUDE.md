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

## Módulo nuevo — checklist de alta ⚠️

Crear el CRUD **no basta**: hay **tres accesos distintos** al mismo módulo y los tres se
mantienen a mano. El módulo de banners (ADR-0030) se entregó con la tarjeta pero sin sidebar
ni paleta, y quedó invisible para quien navega por ahí.

| # | Qué | Dónde |
|---|---|---|
| 1 | Rutas bajo `/administracion` | `routes/web.php` |
| 2 | Vista del módulo | `resources/views/administration/{modulo}/index.blade.php` |
| 3 | Tarjeta en el índice | `resources/views/administration/index.blade.php` |
| 4 | Punto en la «Guía rápida» | misma vista, bloque del final |
| 5 | **Entrada en la barra lateral** | `resources/views/components/layouts/app/sidebar.blade.php`, grupo Administración |
| 6 | **Comando en la paleta** | `resources/views/components/command-palette.blade.php`, `'group' => 'Administración'` |
| 7 | Test de la paleta si es solo-superadmin | `tests/Feature/CommandPaletteTest.php` |
| 8 | Breadcrumb en la vista + `ActivityLogService` en crear/editar/eliminar | vista y controlador |

**La visibilidad por rol debe coincidir en los tres sitios.** Para un módulo solo-superadmin:

```blade
{{-- tarjeta y sidebar --}}
@if(auth()->user()->isSuperadmin())
    <flux:navlist.item :href="route('banners.index')" :current="request()->routeIs('banners.*')" wire:navigate>
        {{ __('Banners') }}
    </flux:navlist.item>
@endif
```

```php
// paleta de comandos: el patrón es ternario a null, no @if
$isSuperadmin
    ? ['label' => 'Banners', 'group' => 'Administración', 'url' => route('banners.index'), 'search' => 'anuncios publicidad patrocinadores']
    : null,
```

El campo `search` son las palabras alternativas por las que se encontrará el comando; ponle los
sinónimos que usaría alguien que no recuerda el nombre exacto del módulo.
