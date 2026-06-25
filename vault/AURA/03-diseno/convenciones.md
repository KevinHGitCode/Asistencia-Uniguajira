---
tipo: diseno
descripcion: Resumen de las convenciones de código (las vinculantes viven en CLAUDE.md)
actualizado: 2026-06-20
---

# Convenciones de código (resumen)

> ⚠️ La **fuente de verdad vinculante** es `CLAUDE.md` en la raíz del repo. Esta nota es un
> **resumen** para orientarse; si hay conflicto, manda `CLAUDE.md`.

## Idioma
- **Código, rutas nombradas, variables, vistas**: español.
- **UI**: español (locale `es` / `es_CO`).

## Backend
- Livewire Volt: componentes de clase en `app/Livewire/`, vistas en `resources/views/livewire/`.
- Lógica reutilizable en `app/Services/`; filtros compartidos en `app/Traits/`.
- Eventos filtrados por `user_id` (propios) y por dependencias del usuario (`belongsToMany`).
- Slugs de evento (`link`) para las URLs públicas del QR.
- Linter: `./vendor/bin/pint` antes de commitear.

## Datos
- Migraciones en orden cronológico en `database/migrations/`.
- Capturar demografía por asistencia en `attendance_details`
  ([[adr-0002-snapshot-demografico-attendance-details]]).

## Administración (patrón de vistas)
- Vistas en `resources/views/administration/` (excepto `formats/`) usan **2 pestañas**:
  *Listado* (tabla con búsqueda Alpine) e *Importar/Exportar* (Excel). El botón "Nuevo X" va
  en el header.
- Clases de exportación en `app/Exports/`; rutas de import/template/export bajo
  `/administracion` con `role:admin`.

## Frontend
- Tailwind v4 (sin config clásico). React solo para estadísticas
  ([[adr-0001-react-islands-estadisticas]]).

## Git / trabajo en equipo
- Branch base: `develop`.
- Convención de ramas: [[convencion-de-ramas]]. Commits: [[convencion-de-commits]].
- Metodología multi-IA: [[reglas-de-oro]].
